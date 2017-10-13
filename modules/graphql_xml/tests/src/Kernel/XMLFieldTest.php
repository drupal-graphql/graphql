<?php

namespace Drupal\Tests\graphql_xml\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\entity_test\Entity\EntityTestWithBundle;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\user\Entity\Role;

/**
 * Test boolean graphql fields.
 *
 * @group graphql_xml
 */
class XMLFieldTest extends KernelTestBase {
  use GraphQLFileTestTrait;

  public static $modules = [
    'system',
    'path',
    'field',
    'text',
    'entity_test',
    'user',
    'graphql',
    'graphql_core',
    'graphql_content',
    'graphql_xml',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('user');
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test_with_bundle');

    Role::load('anonymous')
      ->grantPermission('view test entity')
      ->save();

    EntityTestBundle::create([
      'id' => 'graphql',
      'label' => 'GraphQL',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'xml',
      'type' => 'text_long',
      'entity_type' => 'entity_test_with_bundle',
    ])->save();

    FieldConfig::create([
      'field_name' => 'xml',
      'entity_type' => 'entity_test_with_bundle',
      'bundle' => 'graphql',
      'label' => 'XML',
    ])->save();

    EntityViewMode::create([
      'targetEntityType' => 'entity_test_with_bundle',
      'id' => "entity_test_with_bundle.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'entity_test_with_bundle',
      'bundle' => 'graphql',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('xml', ['type' => 'graphql_xml'])->save();

    $this->container->get('config.factory')->getEditable('graphql_content.schema')
      ->set('types', [
        'entity_test_with_bundle' => [
          'exposed' => TRUE,
          'bundles' => [
            'graphql' => [
              'exposed' => TRUE,
              'view_mode' => 'entity_test_with_bundle.graphql',
            ],
          ],
        ],
      ])
      ->save();
  }

  /**
   * Test tag name retrieval.
   */
  public function testName() {
    $entity = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => '',
      'xml' => '<p>A</p><p>B</p>',
    ]);
    $entity->save();

    $result = $this->executeQueryFile('name.gql', [
      'path' => '/entity_test_with_bundle/' . $entity->id(),
    ]);

    $this->assertEquals([
      ['name' => 'p'],
      ['name' => 'p'],
    ], $result['data']['route']['entity']['xml']['paragraphs']);
  }

  /**
   * Test XPath queries.
   */
  public function testXpath() {
    $entity = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => '',
      'xml' => '<p>A <span>B</span> C</p><p>D</p>',
    ]);
    $entity->save();

    $result = $this->executeQueryFile('xpath.gql', [
      'path' => '/entity_test_with_bundle/' . $entity->id(),
    ]);
    $this->assertEquals('A <span>B</span> C', $result['data']['route']['entity']['xml']['paragraphs'][0]['content']);
    $this->assertEquals('B', $result['data']['route']['entity']['xml']['paragraphs'][0]['spans'][0]['content']);
    $this->assertEquals('D', $result['data']['route']['entity']['xml']['paragraphs'][1]['content']);
    $this->assertEmpty($result['data']['route']['entity']['xml']['paragraphs'][1]['spans']);
  }

  /**
   * Test inner content retrieval.
   */
  public function testContent() {
    $entity = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => '',
      'xml' => '<p>A</p><p>B</p>',
    ]);
    $entity->save();

    $result = $this->executeQueryFile('content.gql', [
      'path' => '/entity_test_with_bundle/' . $entity->id(),
    ]);

    $this->assertEquals('<p>A</p><p>B</p>', $result['data']['route']['entity']['xml']['content']);
  }

  /**
   * Test attribute retrieval.
   */
  public function testAttribute() {
    $entity = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => '',
      'xml' => '<p class="a">A</p><p class="b">B</p>',
    ]);
    $entity->save();

    $result = $this->executeQueryFile('attribute.gql', [
      'path' => '/entity_test_with_bundle/' . $entity->id(),
    ]);

    $this->assertEquals([
      ['class' => 'a'],
      ['class' => 'b'],
    ], $result['data']['route']['entity']['xml']['paragraphs']);
  }

}
