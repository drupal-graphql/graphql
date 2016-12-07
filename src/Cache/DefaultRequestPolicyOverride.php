<?php

namespace Drupal\graphql\Cache;

use Drupal\Core\PageCache\ChainRequestPolicy;
use Drupal\Core\PageCache\RequestPolicy\NoSessionOpen;
use Drupal\Core\Session\SessionConfigurationInterface;
use Drupal\graphql\Cache\RequestPolicy\DenyCommandLine;
use Drupal\graphql\Cache\RequestPolicy\DenyUnsafeMethodUnlessQuery;

/**
 * Overrides the default request policy used by the core page cache.
 */
class DefaultRequestPolicyOverride extends ChainRequestPolicy {

  /**
   * Constructs the default page cache request policy.
   *
   * @param \Drupal\Core\Session\SessionConfigurationInterface $session_configuration
   *   The session configuration.
   */
  public function __construct(SessionConfigurationInterface $session_configuration) {
    $this->addPolicy(new DenyUnsafeMethodUnlessQuery());
    $this->addPolicy(new DenyCommandLine());
    $this->addPolicy(new NoSessionOpen($session_configuration));
  }

}
