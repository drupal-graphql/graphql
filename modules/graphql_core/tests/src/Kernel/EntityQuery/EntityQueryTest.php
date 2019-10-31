<?php

namespace Drupal\Tests\graphql_core\Kernel\EntityQuery;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_core
 */
class EntityQueryTest extends GraphQLContentTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createContentType(['type' => 'a']);
    $this->createContentType(['type' => 'b']);
  }

  /**
   * Test that entity queries work.
   */
  public function testEntityQuery() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'a',
    ]);

    $b = $this->createNode([
      'title' => 'Node B',
      'type' => 'a',
    ]);

    $c = $this->createNode([
      'title' => 'Node C',
      'type' => 'a',
    ]);

    $d = $this->createNode([
      'title' => 'Node D',
      'type' => 'b',
    ]);

    $a->save();
    $b->save();
    $c->save();
    $d->save();

    // TODO: Check cache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheContexts(['user.node_grants:view']);
    $metadata->addCacheTags([
      'node:' . $a->id(),
      'node:' . $b->id(),
      'node:' . $c->id(),
      'node:' . $d->id(),
      'node_list',
    ]);

    $this->assertResults($this->getQueryFromFile('entity_query.gql'), [], [
      'a' => [
        'entities' => [
          ['uuid' => $a->uuid()],
          ['uuid' => $b->uuid()],
          ['uuid' => $c->uuid()],
        ],
        'count' => 3,
      ],
      'b' => [
        'entities' => [
          ['uuid' => $d->uuid()],
        ],
        'count' => 1,
      ],
      'limit' => [
        'entities' => [
          ['uuid' => $a->uuid()],
          ['uuid' => $b->uuid()],
        ],
        'count' => 3,
      ],
      'offset' => [
        'entities' => [
          ['uuid' => $b->uuid()],
          ['uuid' => $c->uuid()],
        ],
        'count' => 3,
      ],
      'offset_limit' => [
        'entities' => [
          ['uuid' => $b->uuid()],
        ],
        'count' => 3,
      ],
      'all_nodes' => [
        'entities' => [
          ['uuid' => $a->uuid()],
          ['uuid' => $b->uuid()],
          ['uuid' => $c->uuid()],
          ['uuid' => $d->uuid()],
        ],
        'count' => 4,
      ],
    ], $metadata);
  }

  /**
   * Make sure entity filters are properly secured.
   */
  public function testFilterSecurity() {
    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts([
      'languages:language_content',
      'languages:language_interface',
      'languages:language_url',
      'user.permissions',
    ]);
    $metadata->addCacheTags(['graphql', 'user_list']);
    $this->assertResults('query { userQuery (filter: { conditions: [ { field: "pass", value: "foo" } ] }) { count } }', [], [
      'userQuery' => [
        // TODO: With proper access checking for filters this value should
        //       become "2" and the entity query field can be marked as secure
        //       again.
        'count' => 0,
      ]
    ], $metadata);
  }

}
