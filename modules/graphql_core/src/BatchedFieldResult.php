<?php

namespace Drupal\graphql_core;

/**
 * A callable lazy result token.
 */
class BatchedFieldResult {

  /**
   * The batched field resolver to get the result from.
   *
   * @var \Drupal\graphql_core\BatchedFieldResolver
   */
  protected $batchedFieldResolver;

  /**
   * The buffer id.
   *
   * @var string
   */
  protected $buffer;

  /**
   * The item id.
   *
   * @var string
   */
  protected $item;

  /**
   * BatchedFieldResult constructor.
   *
   * @param \Drupal\graphql_core\BatchedFieldResolver $batchedFieldResolver
   *   The batched field resolver.
   * @param string $buffer
   *   The buffer id.
   * @param string $item
   *   The buffer item id.
   */
  public function __construct(BatchedFieldResolver $batchedFieldResolver, $buffer, $item) {
    $this->batchedFieldResolver = $batchedFieldResolver;
    $this->buffer = $buffer;
    $this->item = $item;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke() {
    return $this->batchedFieldResolver->resolve($this->buffer, $this->item);
  }

}
