<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\Tests\graphql\Traits\EnableCliCacheTrait;
use Drupal\user\Entity\Role;

/**
 * Test file attachments.
 *
 * @group graphql_image
 */
class RouteEntityTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EnableCliCacheTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'graphql_core',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('node');
    $this->installConfig('filter');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();

  }

  public function testRouteEntity() {
    $node = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $node->save();

    $result = $this->requestWithQueryFile('route_entity.gql', ['path' => '/node/' . $node->id()]);
    $entity = $result['data']['route']['node'];

    $this->assertEquals('Node A', $entity['title']);

    $node->setTitle('Node B');
    $node->save();

    $result = $this->requestWithQueryFile('route_entity.gql', ['path' => '/node/' . $node->id()]);
    $entity = $result['data']['route']['node'];

    $this->assertEquals('Node B', $entity['title']);
  }

}
