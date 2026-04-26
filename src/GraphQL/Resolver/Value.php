<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by returning the fixed value itself.
 */
class Value implements ResolverInterface {

  /**
   * Value constructor.
   *
   * @param mixed $value
   *   Value to be resolved.
   */
  public function __construct(
    protected mixed $value,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    if ($this->value instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($this->value);
    }

    return $this->value;
  }

}
