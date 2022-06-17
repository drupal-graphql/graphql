<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Logger\RfcLoggerTrait;

// The logger trait has a different function signature in Drupal 10, so we need
// this hack to register a compatible trait for both Drupal 9 and 10.
[$version] = explode('.', \Drupal::VERSION, 2);
if ($version >= 10) {
  require_once __DIR__ . '/CompatibleLoggerTrait10.php';
}
else {
  /**
   * Drupal 9 compatible.
   */
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
    public function log($level, $message, array $context = []) {
      $this->loggerCalls[] = [
        'level' => $level,
        'message' => $message,
        'context' => $context,
      ];
    }

  }
}
