<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Callback implements ResolverInterface {

  /**
   * The callback.
   *
   * @var callable
   */
  protected $callback;

  /**
   * Callback constructor.
   *
   * @param callable $callback
   */
  public function __construct(callable $callback) {
    $this->callback = $callback;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
    $result = ($this->callback)($value, $args, $context, $info, $field);
    return $result;
  }

}
