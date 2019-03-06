<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

class DataProducerCallable implements DataProducerInterface {
  /**
   * Construct DataProducerCallable object.
   * @param callable $callback [description]
   */
  public function __construct(callable $callback) {
    $this->callback = $callback;
  }

  /**
   * @inheritdoc.
   */
  public function resolve($value, $args, $context, $info) {
    $result = ($this->callback)($value, $args, $context, $info);
    return $result;
  }

}
