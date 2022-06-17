<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Logger\RfcLoggerTrait;

// The logger trait has a different function signature in Drupal 10, so we need
// this hack to register a compatible trait for both Drupal 9 and 10.
[$version] = explode('.', \Drupal::VERSION, 2);
if ($version >= 10) {
  eval(<<<'CODE'
  use Drupal\Core\Logger\RfcLoggerTrait;

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
    public function log($level, string|\Stringable $message, array $context = []): void {
      $this->loggerCalls[] = [
        'level' => $level,
        'message' => $message,
        'context' => $context,
      ];
    }

  }
CODE);
}
else {
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
