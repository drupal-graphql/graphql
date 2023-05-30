<?php

namespace Drupal\Tests\graphql\Functional\Framework;

use Drupal\graphql\Entity\Server;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\graphql\Functional\GraphQLFunctionalTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the automatic persisted query plugin with page cache.
 *
 * @group graphql
 */
class AutomaticPersistedQueriesWithPageCacheTest extends GraphQLFunctionalTestBase {

  /**
   * The GraphQL server.
   *
   * @var \Drupal\graphql\Entity\Server
   */
  protected $server;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'page_cache',
    'dynamic_page_cache',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Node Type used to create test articles.
    NodeType::create([
      'type' => 'article',
    ])->save();

    // Create some test articles.
    Node::create([
      'nid' => 1,
      'type' => 'article',
      'title' => 'Test Article 1',
    ])->save();

    Node::create([
      'nid' => 2,
      'type' => 'article',
      'title' => 'Test Article 2',
    ])->save();

    $config = [
      'schema' => 'example',
      'name' => 'example',
      'endpoint' => '/graphql-testing',
      'persisted_queries_settings' => [
        'automatic_persisted_query' => [
          'weight' => 0,
        ],
      ],
    ];

    $this->server = Server::create($config);
    $this->server->save();
    \Drupal::service('router.builder')->rebuild();

    $anonymousRole = Role::load(Role::ANONYMOUS_ID);
    $this->grantPermissions($anonymousRole, [
      'execute ' . $this->server->id() . ' persisted graphql requests',
      'execute ' . $this->server->id() . ' arbitrary graphql requests',
    ]
    );
  }

  /**
   * Test APQ with dynamic page cache.
   *
   * Tests that cache context for different variables parameter is correctly
   * added to the dynamic page cache entries.
   */
  public function testPageCacheWithDifferentVariables(): void {
    $query1 = $this->getQueryFromFile('article_title.gql');
    $query2 = $this->getQueryFromFile('article_id.gql');
    $variables1 = '{"id": 1}';
    $variables2 = '{"id": 2}';

    // Test that requests with different variables but same query hash return
    // different responses. Requesting in both instances with query first,
    // to make sure the query is registered.
    $this->apqRequest($this->server->endpoint, $query1, $variables1, TRUE);
    $response = $this->apqRequest($this->server->endpoint, $query1, $variables1);
    $this->assertEquals('TEST ARTICLE 1', $response['data']['article']['title']);

    $this->apqRequest($this->server->endpoint, $query1, $variables2, TRUE);
    $response = $this->apqRequest($this->server->endpoint, $query1, $variables2);
    $this->assertEquals('TEST ARTICLE 2', $response['data']['article']['title']);

    // Test that requests with same variables but different query hash return
    // different responses.
    $this->apqRequest($this->server->endpoint, $query2, $variables2, TRUE);
    $response = $this->apqRequest($this->server->endpoint, $query2, $variables2);
    $this->assertEquals(2, $response['data']['article']['id']);

  }

  /**
   * Test PersistedQueryNotFound error is not cached in page cache.
   */
  public function testPersistedQueryNotFoundNotCached(): void {
    $query = $this->getQueryFromFile('article_title.gql');
    $variables = '{"id": 1}';

    // The first request should return an PersistedQueryNotFound error.
    $this->assertPersistedQueryNotFound($query, $variables);

    // Retry with the query included.
    $response = $this->apqRequest($this->server->endpoint, $query, $variables, TRUE);
    $this->assertEquals('TEST ARTICLE 1', $response['data']['article']['title']);

    // Finally, a request without the query should return the correct data.
    $response = $this->apqRequest($this->server->endpoint, $query, $variables);
    $this->assertEquals('TEST ARTICLE 1', $response['data']['article']['title']);
  }

}
