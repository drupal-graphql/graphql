<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by calling a chain of resolvers after each other.
 */
class Composite implements ResolverInterface {

  /**
   * Composite constructor.
   *
   * @param array<\Drupal\graphql\GraphQL\Resolver\ResolverInterface> $resolvers
   *   DataProducerProxy objects.
   */
  public function __construct(
    protected array $resolvers,
  ) {
  }

  /**
   * Add one more producer.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *   DataProducerProxy object.
   */
  public function add(ResolverInterface $resolver): void {
    $this->resolvers[] = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $resolvers = $this->resolvers;
    while ($resolver = array_shift($resolvers)) {
      $value = $resolver->resolve($value, $args, $context, $info, $field);

      if ($value instanceof SyncPromise) {
        return DeferredUtility::returnFinally($value, function ($value) use ($resolvers, $args, $context, $info, $field) {
          return isset($value) ? (new Composite($resolvers))->resolve($value, $args, $context, $info, $field) : NULL;
        });
      }
    }

    return $value;
  }

}
