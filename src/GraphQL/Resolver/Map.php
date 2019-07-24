<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Map implements ResolverInterface {

  /**
   * Resolver to tap.
   *
   * @var mixed
   */
  protected $resolver;

  /**
   * Map constructor.
   *
   * @param $resolver
   */
  public function __construct($resolver) {
    $this->resolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
    if (!is_iterable($value)) {
      return NULL;
    }

    $array = is_array($value) ? $value : iterator_to_array($value);
    return array_map(function ($item) use ($args, $context, $info, $field) {
      return $this->resolver->resolve($item, $args, $context, $info, $field);
    }, $array);
  }

}
