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
   * The entity query object.
   */
  protected QueryInterface $query;

  /**
   * The entity buffer service.
   */
  protected EntityBuffer $entityBuffer;

  /**
   * QueryConnection constructor.
   */
  public function __construct(QueryInterface $query, EntityBuffer $entityBuffer) {
    $this->query = $query;
    $this->entityBuffer = $entityBuffer;
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
