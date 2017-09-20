<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Trait to automatically bypass permissions in GraphQL tests.
 */
trait ByPassAccessTrait {

  /**
   * Bypass user access.
   *
   * @before
   */
  protected function byPassAccess() {
    // Replace the current user with one that is allowed to do GraphQL requests.
    $user = $this->prophesize(AccountProxyInterface::class);
    $user->hasPermission('execute graphql requests')
      ->willReturn(AccessResult::allowed());
    $user->hasPermission('bypass graphql field security')
      ->willReturn(AccessResult::allowed());
    $user->id()->willReturn(0);
    $user->isAnonymous()->willReturn(TRUE);
    $this->container->set('current_user', $user->reveal());
  }
}