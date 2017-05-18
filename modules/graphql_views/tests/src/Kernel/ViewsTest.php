<?php

namespace Drupal\Tests\graphql_views\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

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
    'graphql_test_views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('view');
    $this->installConfig(['node', 'filter', 'views', 'graphql_test_views']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test that the view returns both nodes.
   */
  public function testSimpleView() {
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

    $result = $this->executeQueryFile('simple.gql');

    $this->assertEquals([
      [
        'entityLabel' => 'Node A',
      ], [
        'entityLabel' => 'Node B',
      ],
    ], $result['data']['graphqlTestSimpleView']);
  }

  /**
   * Test paging support.
   */
  public function testPagedView() {
    $nodes = [];
    foreach (range(1, 10) as $index) {
      $nodes[$index] = $this->createNode([
        'title' => 'Node ' . $index,
        'type' => 'test',
      ]);
      $nodes[$index]->save();
    }

    $result = $this->executeQueryFile('paged.gql');
    $this->assertEquals([
      'page_one' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node 1'],
          ['entityLabel' => 'Node 2'],
        ],
      ],
      'page_two' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node 3'],
          ['entityLabel' => 'Node 4'],
        ],
      ],
      'page_three' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node 5'],
          ['entityLabel' => 'Node 6'],
          ['entityLabel' => 'Node 7'],
          ['entityLabel' => 'Node 8'],
          ['entityLabel' => 'Node 9'],
          ['entityLabel' => 'Node 10'],
        ],
      ],
    ], $result['data'], 'Paged views return the correct results.');
  }

}
