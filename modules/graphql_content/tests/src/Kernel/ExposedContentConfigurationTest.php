<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class ExposedContentConfigurationTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_core',
    'graphql_content',
    'graphql_content_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installConfig(['text']);
    $this->installEntitySchema('node');

    $this->createContentType([
      'type' => 'test',
    ]);

    FieldStorageConfig::create([
      'field_name' => 'field_keywords',
      'entity_type' => 'node',
      'type' => 'text',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'test',
      'field_name' => 'field_keywords',
      'label' => 'Keywords',
    ])->save();

    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();
    entity_get_display('node', 'test', 'graphql')
      ->setComponent('field_keywords')
      ->setComponent('test')
      ->save();

    EntityViewMode::create(['id' => 'node.default', 'targetEntityType' => 'node'])->save();
    entity_get_display('node', 'test', 'default')
      ->setComponent('body')
      ->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();
  }

  /**
   * Ensure that unexposed entity types are not exposed.
   */
  public function testUnexposedEntity() {
    $schema = $this->executeQueryFile('schema.gql');
    $types = array_filter($schema['data']['__schema']['types'], function($type) {
      return in_array($type['name'], ['Node', 'NodeTest']);
    });

    // TODO Adjust the test to the new, permission-based way of generating the schema.

    // $this->assertEmpty($types, 'No types exposed.');
  }

  /**
   * Test if the interface for nodes is created.
   */
  public function testNodeInterface() {
    $this->configFactory->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
        ],
      ])->save();

    $schema = $this->executeQueryFile('schema.gql');
    $types = array_filter($schema['data']['__schema']['types'], function($type) {
      return in_array($type['name'], ['Node', 'NodeTest']);
    });

    // TODO Adjust the test to the new, permission-based way of generating the schema.

    // $this->assertEquals(1, count($types), 'No types exposed.');
  }

  /**
   * Test if the node bundle types are created.
   */
  public function testNodeType() {
    $this->configFactory->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
            ],
          ],
        ],
      ])->save();

    $schema = $this->executeQueryFile('schema.gql');
    $types = array_filter($schema['data']['__schema']['types'], function($type) {
      return in_array($type['name'], ['Node', 'NodeTest']);
    });
    $this->assertEquals(2, count($types), 'No types exposed.');
  }

  /**
   * Test if no fields are exposed.
   */
  public function testUnexposedFields() {
    $this->configFactory->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
              'view_mode' => '__none__',
            ],
          ],
        ],
      ])->save();

    $schema = $this->executeQueryFile('introspect.gql');
    $result = $this->processIntrospection($schema['data']['__schema']);

    // TODO Adjust the test to the new, permission-based way of generating the schema.

    // $this->assertArrayNotHasKey('fieldKeywords', $result['types']['NodeTest:OBJECT']['fields']);
  }

  /**
   * Test if view mode fields are properly exposed.
   */
  public function testExposedFields() {
    $this->configFactory->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
              'view_mode' => 'node.graphql',
            ],
          ],
        ],
      ])->save();

    $schema = $this->executeQueryFile('introspect.gql');
    $result = $this->processIntrospection($schema['data']['__schema']);
    $this->assertArrayHasKey('fieldKeywords', $result['types']['NodeTest:OBJECT']['fields']);
    $this->assertArrayHasKey('test', $result['types']['NodeTest:OBJECT']['fields']);

    // TODO Adjust the test to the new, permission-based way of generating the schema.

    // $this->assertArrayNotHasKey('body', $result['types']['NodeTest:OBJECT']['fields']);
  }

  /**
   * Test if unknown view modes fall back to default.
   */
  public function testFallback() {
    $this->configFactory->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
              'view_mode' => 'node.idontexist',
            ],
          ],
        ],
      ])->save();

    $schema = $this->executeQueryFile('introspect.gql');
    $result = $this->processIntrospection($schema['data']['__schema']);
    $this->assertArrayHasKey('body', $result['types']['NodeTest:OBJECT']['fields']);
  }

  /**
   * Recursively add readable keys to sequential arrays to ease testing.
   *
   * TODO: Move this into a trait. Could be useful in other tests.
   */
  protected function processIntrospection($values) {
    if (!is_array($values)) {
      return $values;
    }
    if (!count(array_filter(array_keys($values), 'is_string'))) {
      $values = array_combine(array_map([$this, 'generateKey'], $values), array_values($values));
    }
    return array_map([$this, 'processIntrospection'], $values);
  }

  /**
   * Generate the key for a numerically indexed value.
   */
  protected function generateKey($value) {
    if (array_key_exists('kind', $value) && array_key_exists('name', $value)) {
      return $value['name'] . ':' . $value['kind'];
    }
    if (array_key_exists('name', $value)) {
      return $value['name'];
    }
    return md5(serialize($value));
  }

}
