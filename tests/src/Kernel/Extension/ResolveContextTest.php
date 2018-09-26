<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Test contextual arguments in fields.
 *
 * @group graphql
 */
class ResolveContextTest extends GraphQLTestBase {

  /**
   * Test manual context handling.
   */
  public function testResolveContext() {
    $this->mockType('test', ['name' => 'Test']);

    $this->mockField('a', [
      'name' => 'a',
      'type' => 'Test',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      $context->setContext('context', 'test', $info);
      yield 'foo';
    });

    $this->mockField('b', [
      'name' => 'b',
      'type' => 'String',
      'parents' => ['Test'],
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      yield $context->getContext('context', $info);
    });

    $this->mockField('c', [
      'name' => 'c',
      'type' => 'String',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      yield $context->getContext('context', $info);
    });

    $query = <<<GQL
query {
  a {
    b
  }
  c
}
GQL;
    $this->assertResults($query, [], [
      'a' => [
        'b' => 'test',
      ],
      'c' => NULL,
    ], $this->defaultCacheMetaData());

  }


  /**
   * Test manual context handling.
   */
  public function testContextualArguments() {
    $this->mockType('test', ['name' => 'Test']);

    $this->mockField('a', [
      'name' => 'a',
      'type' => 'Test',
      'arguments' => [
        'context' => 'String',
      ],
      'contextual_arguments' => ['context'],
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      yield 'foo';
    });

    $this->mockField('b', [
      'name' => 'b',
      'type' => 'String',
      'arguments' => [
        'context' => 'String',
      ],
      'contextual_arguments' => ['context'],
      'parents' => ['Test'],
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      yield $args['context'];
    });

    $this->mockField('c', [
      'name' => 'c',
      'type' => 'String',
      'arguments' => [
        'context' => 'String',
      ],
      'contextual_arguments' => ['context'],
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      yield $args['context'];
    });

    $query = <<<GQL
query {
  a (context: "test") {
    b
  }
  c
}
GQL;
    $this->assertResults($query, [], [
      'a' => [
        'b' => 'test',
      ],
      'c' => NULL,
    ], $this->defaultCacheMetaData());

  }
}
