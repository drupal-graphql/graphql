<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql;

/**
 * A simple count for use in tests.
 */
class TestInvocationCounter {

  /**
   * The current count.
   */
  protected int $count = 0;

  /**
   * Increment the count.
   */
  public function increment(): void {
    $this->count++;
  }

  /**
   * Get the count.
   */
  public function getCount(): int {
    return $this->count;
  }

}
