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
  protected $letters = ['A', 'B', 'C', 'A', 'B', 'C', 'A', 'B', 'C'];

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
        'count' => count($this->letters),
        'results' => [
          ['entityLabel' => 'Node A'],
          ['entityLabel' => 'Node B'],
        ],
      ],
      'page_two' => [
        'count' => count($this->letters),
        'results' => [
          ['entityLabel' => 'Node C'],
          ['entityLabel' => 'Node A'],
        ],
      ],
      'page_three' => [
        'count' => count($this->letters),
        'results' => [
          ['entityLabel' => 'Node A'],
          ['entityLabel' => 'Node B'],
          ['entityLabel' => 'Node C'],
        ],
      ],
      'page_four' => [
        'count' => count($this->letters),
        'results' => [
          ['entityLabel' => 'Node C'],
        ],
      ],
    ], $result['data'], 'Paged views return the correct results.');
  }

  /**
   * Test sorting behavior.
   */
  public function testSortedView() {
    $result = $this->executeQueryFile('sorted.gql');
    $this->assertEquals([
      'default' => [
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node B'],
        ['entityLabel' => 'Node C'],
      ],
      'asc' => [
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node A'],
      ],
      'desc' => [
        ['entityLabel' => 'Node C'],
        ['entityLabel' => 'Node C'],
        ['entityLabel' => 'Node C'],
      ],
      'asc_nid' => [
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node B'],
        ['entityLabel' => 'Node C'],
      ],
      'desc_nid' => [
        ['entityLabel' => 'Node C'],
        ['entityLabel' => 'Node B'],
        ['entityLabel' => 'Node A'],
      ],
    ], $result['data'], 'Sorting works as expected.');
  }

  /**
   * Test filter behavior.
   */
  public function testFilteredView() {
    $result = $this->executeQueryFile('filtered.gql');
    $this->assertEquals([
      'default' => [
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node A'],
        ['entityLabel' => 'Node A'],
      ],
    ], $result['data'], 'Filtering works as expected.');
  }

}
