<?php

namespace Drupal\Tests\graphql_views\Kernel;

use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;
use Drupal\views\Entity\View;

/**
 * Test views support in GraphQL.
 *
 * @group graphql_views
 */
class ViewsTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'views',
    'graphql_content',
    'graphql_views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('view');
    $this->installConfig(['node', 'filter', 'views']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    $view = View::create(['id' => 'my_view', 'base_table' => 'node']);
    $view->addDisplay('graphql', NULL, 'my_display');
    $view->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test that the view returns both nodes.
   */
  public function testView() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $b = $this->createNode([
      'title' => 'Node B',
      'type' => 'test',
    ]);

    $a->save();
    $b->save();

    $result = $this->executeQueryFile('view.gql');

    $this->assertEquals([[
      'entityLabel' => 'Node A',
    ], [
      'entityLabel' => 'Node B',
    ]], $result['data']['myViewMyDisplayView']);
  }
}
