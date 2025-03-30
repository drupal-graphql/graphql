<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use GraphQL\Deferred;

/**
 * Helper class that wraps entity queries.
 */
class QueryConnection {

  /**
   * The entity query object.
   */
  protected QueryInterface $query;

  /**
   * QueryConnection constructor.
   */
  public function __construct(QueryInterface $query) {
    $this->query = $query;
  }

  /**
   * Returns the total number of entities.
   */
  public function total(): int {
    $query = clone $this->query;
    $query->range(NULL, NULL)->count();
    /** @var int */
    return $query->execute();
  }

  /**
   * Returns a callback that resolves in a deferred fashion.
   */
  public function items(): array|Deferred {
    $result = $this->query->execute();
    if (empty($result)) {
      return [];
    }

    $buffer = \Drupal::service('graphql.buffer.entity');
    $callback = $buffer->add($this->query->getEntityTypeId(), array_values($result));
    return new Deferred(function () use ($callback) {
      return $callback();
    });
  }

}
