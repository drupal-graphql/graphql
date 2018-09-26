<?php

namespace Drupal\Tests\graphql_core\Kernel\Context;

use Drupal\node\Entity\Node;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test full stack retrieval of a node context.
 *
 * @group graphql_core
 */
class NodeContextTest extends GraphQLContentTestBase {

  /**
   * Regression test for unhandled logic exceptions.
   *
   * Leaking cache metadata.
   */
  public function testNodeContext() {
    $nodeId = Node::create([
      'title' => 'Test',
      'type' => 'test',
    ])->save();

    $query = <<<GQL
query (\$path: String!) {
  route(path: \$path) {
    ... on InternalUrl {
      nodeContext {
        entityLabel
      }
    }
  }
}
GQL;

    $this->query($query, ['path' => '/node/' . $nodeId]);
  }

}
