<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use GraphQL\Deferred;

/**
 * Helper class that wraps entity queries.
 */
class QueryConnection {

  /**
   * QueryConnection constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   */
  public function __construct(
    protected QueryInterface $query,
    protected EntityBuffer $entityBuffer,
  ) {
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

    $callback = $this->entityBuffer->add($this->query->getEntityTypeId(), array_values($result));
    return new Deferred(function () use ($callback) {
      return $callback();
    });
  }

}
