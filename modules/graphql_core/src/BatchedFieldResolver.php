<?php

namespace Drupal\graphql_core;

use Drupal\graphql_core\GraphQL\BatchedFieldInterface;

/**
 * Queueing service for deferred field resolution.
 */
class BatchedFieldResolver {

  /**
   * The queues of field evaluation requests.
   *
   * @var array
   */
  protected $buffers;

  /**
   * Add a new field evaluation to a buffer.
   *
   * @param \Drupal\graphql_core\GraphQL\BatchedFieldInterface $batchedField
   *   The field instance.
   * @param mixed $value
   *   The parent value.
   * @param array $args
   *   The arguments it has been invoked with.
   *
   * @return \Drupal\graphql_core\BatchedFieldResult
   *   A lazily evaluated batched field result.
   */
  public function add(BatchedFieldInterface $batchedField, $value, array $args) {
    $buffer = $batchedField->getBatchId($value, $args);
    $this->buffers[$buffer][] = [
      'parent' => $value,
      'arguments' => $args,
      'field' => $batchedField,
    ];
    return new BatchedFieldResult($this, $buffer, max(array_keys($this->buffers[$buffer])));
  }

  /**
   * Retrieve a result for a specific ticket.
   *
   * @param string $buffer
   *   The batch queue.
   * @param string $item
   *   The batch item identifier.
   *
   * @return mixed
   *   The evaluation result.
   *
   * @throws \Exception
   *   In case the batch item is not valid any more.
   */
  public function resolve($buffer, $item) {
    if (!array_key_exists($buffer, $this->buffers) || !array_key_exists($item, $this->buffers[$buffer])) {
      throw new \Exception(sprintf("Requesting unregistered batched result: %s[%s].", $buffer, $item));
    }

    if (!array_key_exists('result', $this->buffers[$buffer][$item])) {
      foreach ($this->buffers[$buffer][$item]['field']->resolveBatch($this->buffers[$buffer]) as $index => $result) {
        $this->buffers[$buffer][$index]['result'] = $result;
      }
    }

    $result = $this->buffers[$buffer][$item]['result'];
    unset($this->buffers[$buffer][$item]);
    return $result;
  }

}
