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
    'graphql_views_test',
  ];

  /**
   * A List of letters.
   *
   * @var string[]
   */
  protected $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('view');
    $this->installConfig(['node', 'filter', 'views', 'graphql_views_test']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    foreach ($this->letters as $index => $letter) {
      $this->createNode([
        'title' => 'Node ' . $letter,
        'type' => 'test',
      ])->save();
    }
  }

  /**
   * Test that the view returns both nodes.
   */
  public function testSimpleView() {

    $result = $this->executeQueryFile('simple.gql');

    $this->assertEquals([
      [
        'entityLabel' => 'Node A',
      ], [
        'entityLabel' => 'Node B',
      ], [
        'entityLabel' => 'Node C',
      ],
    ], $result['data']['graphqlTestSimpleView']);
  }

  /**
   * Test paging support.
   */
  public function testPagedView() {
    $result = $this->executeQueryFile('paged.gql');
    $this->assertEquals([
      'page_one' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node A'],
          ['entityLabel' => 'Node B'],
        ],
      ],
      'page_two' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node C'],
          ['entityLabel' => 'Node D'],
        ],
      ],
      'page_three' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node G'],
          ['entityLabel' => 'Node H'],
          ['entityLabel' => 'Node I'],
        ],
      ],
      'page_four' => [
        'count' => 10,
        'results' => [
          ['entityLabel' => 'Node J'],
        ],
      ],
    ], $result['data'], 'Paged views return the correct results.');
  }

}
