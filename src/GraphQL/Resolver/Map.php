<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Execute a resolver for each item in the given list.
 */
class Map implements ResolverInterface {

  /**
   * Resolver to tap.
   */
  protected mixed $resolver;

  /**
   * Map constructor.
   */
  public function __construct(ResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    if (!is_iterable($value)) {
      return NULL;
    }

    $array = is_array($value) ? $value : iterator_to_array($value);
    return array_map(function ($item) use ($args, $context, $info, $field) {
      return $this->resolver->resolve($item, $args, $context, $info, $field);
    }, $array);
  }

}
