<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the automatic persisted query plugin.
 *
 * @group graphql
 */
class AutomaticPersistedQueriesTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'page_cache',
  ];

  /**
   * Test plugin.
   */
  protected PersistedQueryPluginInterface $pluginApq;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->configureCachePolicy();
    $schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        field_one: String
      }
  GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'field_one', 'this is the field one');

    /** @var \Drupal\graphql\Plugin\DataProducerPluginManager $manager */
    $manager = $this->container->get('plugin.manager.graphql.persisted_query');

    $this->pluginApq = $manager->createInstance('automatic_persisted_query');
  }

  /**
   * Test the automatic persisted queries plugin.
   */
  public function testAutomaticPersistedQueries(): void {
    // Before adding the persisted query plugins to the server, we want to make
    // sure that there are no existing plugins already there.
    $this->server->removeAllPersistedQueryInstances();
    $this->server->addPersistedQueryInstance($this->pluginApq);
    $this->server->save();

    $query = 'query { field_one } ';
    $extensions = ['persistedQuery' => ['sha256Hash' => 'some random hash']];

    // Check we get PersistedQueryNotFound.
    $result = $this->query(NULL, $this->server, [], $extensions);

    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame([
      'errors' => [
        [
          'message' => 'PersistedQueryNotFound',
        ],
      ],
    ], json_decode($result->getContent(), TRUE));

    // Post query to endpoint with a not matching hash.
    $result = $this->query($query, $this->server, [], $extensions, FALSE, Request::METHOD_POST);

    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame([
      'errors' => [
        [
          'message' => 'Provided sha does not match query',
        ],
      ],
    ], json_decode($result->getContent(), TRUE));

    // Post query to endpoint to get the result and cache it.
    $extensions['persistedQuery']['sha256Hash'] = hash('sha256', $query);
    $result = $this->query($query, $this->server, [], $extensions, FALSE, Request::METHOD_POST);

    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame(['data' => ['field_one' => 'this is the field one']], json_decode($result->getContent(), TRUE));

    // Execute first GET request again.
    $result = $this->query(NULL, $this->server, [], $extensions);
    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame(['data' => ['field_one' => 'this is the field one']], json_decode($result->getContent(), TRUE));
  }

  /**
   * Tests APQ requests still enforce disabled introspection validation.
   */
  public function testAutomaticPersistedQueryDoesNotBypassDisabledIntrospection(): void {
    // Before adding the persisted query plugins to the server, we want to make
    // sure that there are no existing plugins already there.
    $this->server->removeAllPersistedQueryInstances();
    $this->server->addPersistedQueryInstance($this->pluginApq);
    $this->server->set('disable_introspection', TRUE);
    $this->server->save();

    $query = '{ __schema { queryType { name } } }';
    $payload = [
      'query' => $query,
      'queryId' => 'anything',
      'extensions' => [
        'persistedQuery' => [
          'sha256Hash' => hash('sha256', $query),
        ],
      ],
    ];

    $request = Request::create(
      $this->server->get('endpoint'),
      Request::METHOD_POST,
      [],
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      json_encode($payload)
    );

    $result = $this->container->get('http_kernel')->handle($request);

    $this->assertSame(200, $result->getStatusCode());
    $data = json_decode($result->getContent(), TRUE);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('errors', $data);
    $this->assertNotEmpty($data['errors']);
    $this->assertSame(
      'GraphQL introspection is not allowed, but the query contained __schema or __type',
      $data['errors'][0]['message']
    );
  }

}
