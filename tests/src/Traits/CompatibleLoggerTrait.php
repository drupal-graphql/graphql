<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Logger\RfcLoggerTrait;

[$version] = explode('.', \Drupal::VERSION, 2);
if ($version >= 10) {
  eval(<<<'CODE'
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
