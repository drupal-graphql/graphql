<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use GraphQL\Deferred;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Data producers composition.
 */
class DataProducerComposite implements DataProducerInterface {

  /**
   * DataProducerProxy objects.
   *
   * @var array
   */
  private $resolvers = [];

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
   * @param DataProducerProxy $resolver
   *   DataProducerProxy object.
   */
  public function add(DataProducerProxy $resolver) {
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
          return isset($value) ? (new DataProducerComposite($resolvers))->resolve($value, $args, $context, $info) : NULL;
        });
      }
    }
    return $value;
  }

}
