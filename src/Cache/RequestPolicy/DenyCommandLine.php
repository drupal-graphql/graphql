<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reject when running from the command line.
 */
class DenyCommandLine implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($this->isCli()) {
      return static::DENY;
    }

    return NULL;
  }

  /**
   * Indicates whether this is a CLI request.
   */
  protected function isCli() {
    return PHP_SAPI === 'cli';
  }

}
