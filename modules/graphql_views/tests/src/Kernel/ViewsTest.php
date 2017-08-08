<?php

namespace Drupal\Tests\graphql_views\Kernel;

/**
 * Test views support in GraphQL.
 *
 * @group graphql_views
 */
class ViewsTest extends ViewsTestBase {

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
      ['entityLabel' => 'Node A'],
      ['entityLabel' => 'Node A'],
      ['entityLabel' => 'Node A'],
    ], $result['data']['default'], 'Filtering works as expected.');
  }

  /**
   * Test filter behavior.
   */
  public function testMultiValueFilteredView() {
    $result = $this->executeQueryFile('filtered.gql');
    $this->assertEquals([
      ['entityLabel' => 'Node A'],
      ['entityLabel' => 'Node B'],
      ['entityLabel' => 'Node A'],
      ['entityLabel' => 'Node B'],
      ['entityLabel' => 'Node A'],
      ['entityLabel' => 'Node B'],
    ], $result['data']['multi'], 'Filtering works as expected.');
  }

}
