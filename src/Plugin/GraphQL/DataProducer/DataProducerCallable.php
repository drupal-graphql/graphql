<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class handles callable data producers.
 */
class DataProducerCallable implements DataProducerInterface {

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
