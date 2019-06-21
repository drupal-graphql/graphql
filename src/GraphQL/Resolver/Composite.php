<?php

namespace Drupal\graphql\GraphQL\Resolver;

use GraphQL\Deferred;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Composite implements ResolverInterface {

  /**
   * DataProducerProxy objects.
   *
   * @var array
   */
  protected $resolvers = [];

  /**
   * Construct DataProducerComposit object.
   *
   * @param array $resolvers
   *   Array of Data Producers.
   */
  public function __construct(array $resolvers) {
    $this->resolvers = $resolvers;
  }

  /**
   * Add one more producer.
   *
   * @param ResolverProxy $resolver
   *   DataProducerProxy object.
   */
  public function add(ResolverProxy $resolver) {
    $this->resolvers[] = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $resolvers = $this->resolvers;
    while ($resolver = array_shift($resolvers)) {
      $value = $resolver->resolve($value, $args, $context, $info);

      if ($value instanceof Deferred) {
        return DeferredUtility::returnFinally($value, function ($value) use ($resolvers, $args, $context, $info) {
          return isset($value) ? (new Composite($resolvers))->resolve($value, $args, $context, $info) : NULL;
        });
      }
    }

    return $value;
  }

}
