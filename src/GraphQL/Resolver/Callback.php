<?php

namespace Drupal\graphql\GraphQL\Resolver;

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
   * Construct DataProducerCallable object.
   *
   * @param callable $callback
   *   Callback.
   */
  public function __construct(callable $callback) {
    $this->callback = $callback;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $result = ($this->callback)($value, $args, $context, $info);
    return $result;
  }

}
