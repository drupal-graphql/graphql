<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test file attachments.
 *
 * @group graphql_core
 */
class RouteEntityTest extends GraphQLContentTestBase {

  public function testRouteEntity() {
    $node = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $node->save();

    $node->addTranslation('fr', [
      'title' => 'Node A french',
    ])->save();

    $query = $this->getQueryFromFile('route_entity.gql');
    $vars = ['path' => '/node/' . $node->id()];

    // TODO: Check cache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags([
      'entity_field_info',
      'node:1',
    ]);

    $this->assertResults($query, $vars, [
      'route' => [
        'node' => [
          'title' => 'Node A',
        ],
      ],
    ], $metadata);

    $node->setTitle('Node B');
    $node->save();

    $this->assertResults($query, $vars, [
      'route' => [
        'node' => [
          'title' => 'Node B',
        ],
      ],
    ], $metadata);
  }

}
