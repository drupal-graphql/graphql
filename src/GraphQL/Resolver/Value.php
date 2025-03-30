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
   * Value to be resolved.
   */
  protected mixed $value;

  /**
   * Value constructor.
   */
  public function __construct(mixed $value) {
    $this->value = $value;
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
