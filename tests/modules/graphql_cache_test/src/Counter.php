<?php

namespace Drupal\graphql_cache_test;

/**
 * A counter service that increases every time it's accessed.
 */
class Counter {
  /**
   * The current counter value.
   *
   * @var int
   */
  protected static $count = 0;

  /**
   * Increase the counter and return it.
   *
   * @param int $amount
   *   The amount to increase the counter.
   *
   * @return int
   *   The current count value.
   */
  public function count($amount = 1) {
    static::$count += $amount;
    return static::$count;
  }

}
