<?php

namespace Drupal\Tests\graphql_config\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_config
 */
class EntityByIdTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->createContentType(['type' => 'test']);

    $this->container->get('config.factory')->getEditable('graphql_config.schema')
      ->set('types', [
        'node_type' => [
          'exposed' => TRUE,
        ],
      ])->save();
  }

  /**
   * Test that the node type configuration entity is retrieved correctly.
   */
  public function testEntityById() {
    $result = $this->executeQueryFile('config_entity_by_id.gql', [
      'id' => 'test',
    ]);
    $this->assertEquals(['entityId' => 'test'], $result['data']['nodeTypeById']);
  }

}
