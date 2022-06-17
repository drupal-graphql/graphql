<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Logger\RfcLoggerTrait;

/**
 * Drupal 10 compatible.
 */
// phpcs:ignore
trait CompatibleLoggerTrait {
  use RfcLoggerTrait;

  /**
   * Collected loggers calls.
   *
   * @var array
   */
  protected $loggerCalls = [];

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->loggerCalls[] = [
      'level' => $level,
      'message' => $message,
      'context' => $context,
    ];
  }

}
