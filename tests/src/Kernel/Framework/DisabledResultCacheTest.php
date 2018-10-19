<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\Entity\Server;

/**
 * Test disabled result cache.
 *
 * @group graphql
 */
class DisabledResultCacheTest extends GraphQLTestBase {

  /**
   * Test if disabling the result cache has the desired effect.
   */
  public function testDisabledCache() {
    $gql_schema = <<<GQL
      type Query {
        root: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema(), TRUE);

    $dummy_object = $this->getMockBuilder(Server::class)
      ->disableOriginalConstructor()
      ->setMethods(['id'])
      ->getMock();
    $dummy_object->expects($this->exactly(2))
      ->method('id')
      ->willReturn('test');

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'parent' => 'Query',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($dummy_object) {
      return $dummy_object->id();
    });

    // The first request that is not supposed to be cached.
    $this->query('{ root }');

    // This should invoke the processor a second time.
    $this->query('{ root }');
  }

}
