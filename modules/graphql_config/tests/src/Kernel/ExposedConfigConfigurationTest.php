<?php

namespace Drupal\Tests\graphql_config\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;

/**
 * Test basic entity fields.
 *
 * @group graphql_config
 */
class ExposedConfigConfigurationTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_config',
  ];

  /**
   * @var \Drupal\node\Entity\NodeType
   */
  protected $content_type;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');

    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->content_type = $this->createContentType(['type' => 'test']);
  }

  /**
   * Ensure that unexposed entity types are not exposed.
   */
  public function testUnexposedEntity() {
    $schema = $this->executeQueryFile('schema.gql');
    $types = array_filter($schema['data']['__schema']['types'], function ($type) {
      return ($type['name'] == 'NodeType');
    });
    $this->assertEmpty($types, 'Node types unexposed.');
  }

  /**
   * Test if the interface for node types is created.
   */
  public function testNodeInterface() {
    $this->container->get('config.factory')->getEditable('graphql_config.schema')
      ->set('types', [
        'node_type' => [
          'exposed' => TRUE,
        ],
      ])->save();

    $schema = $this->executeQueryFile('schema.gql');
    $types = array_filter($schema['data']['__schema']['types'], function ($type) {
      return ($type['name'] == 'NodeType');
    });
    $this->assertEquals(1, count($types), 'Node types exposed.');
  }

}
