<?php

namespace Drupal\graphql_core;

use Drupal\graphql_core\GraphQL\BatchedFieldInterface;

/**
 * Queueing service for deferred field resolution.
 */
class BatchedFieldResolver {
  protected $queue;

  /**
   * Add a new field evaluation to the queue.
   *
   * @param \Drupal\graphql_core\GraphQL\BatchedFieldInterface $batchedField
   *   The field instance.
   * @param mixed $value
   *   The parent value.
   * @param array $args
   *   The arguments it has been invoked with.
   *
   * @return array
   *   A ticket that has to be used to retrieve the evaluation result.
   */
  public function enqueue(BatchedFieldInterface $batchedField, $value, array $args) {
    $id = $batchedField->getBatchId($value, $args);
    $this->queue[$id][] = [
      'parent' => $value,
      'arguments' => $args,
      'field' => $batchedField,
    ];
    return [$id, max(array_keys($this->queue[$id]))];
  }

  /**
   * Retrieve a result for a specific ticket.
   *
   * @param array $key
   *   The deferred evaluation ticket.
   *
   * @return mixed
   *   The evaluation result.
   *
   * @throws \Exception
   *   In case the ticket is not valid any more.
   */
  public function resolve(array $key) {
    list($id, $item) = $key;
    if (!array_key_exists($id, $this->queue) || !array_key_exists($item, $this->queue[$id])) {
      throw new \Exception("Requesting unregistered batched result: " . serialize($key));
    }
    if (!array_key_exists('result', $this->queue[$id][$item])) {
      foreach ($this->queue[$id][$item]['field']->prepareBatch($this->queue[$id]) as $index => $result) {
        $this->queue[$id][$index]['result'] = $result;
      }
    }
    $result = $this->queue[$id][$item]['result'];
    unset($this->queue[$id][$item]);
    return $result;
  }

}
