<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Default value resolver.
 *
 * Allows to fall back if a value is NULL.
 */
class DefaultValue implements ResolverInterface {

  /**
   * DefaultValue constructor.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $value
   *   The initial value to check.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $default
   *   The fallback value returned if the initial one resolves to NULL.
   */
  public function __construct(
    protected ResolverInterface $value,
    protected ResolverInterface $default,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $result = $this->value->resolve($value, $args, $context, $info, $field);
    if ($result === NULL) {
      return $this->default->resolve($value, $args, $context, $info, $field);
    }

    if ($result instanceof SyncPromise) {
      return DeferredUtility::returnFinally($result, function ($current) use ($value, $args, $context, $info, $field) {
        if ($current === NULL) {
          return $this->default->resolve($value, $args, $context, $info, $field);
        }
        return $current;
      });
    }
    return $result;
  }

}
