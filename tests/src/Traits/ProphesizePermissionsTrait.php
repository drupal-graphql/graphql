<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;
use Prophecy\Argument;

/**
 * Trait to simulate user permissions.
 */
trait ProphesizePermissionsTrait {

  protected $accountProphecy;

  /**
   * Set the prophesized permissions.
   *
   * @return string[]
   *   The permissions to set on the prophesized user.
   */
  protected function userPermissions() {
    return [
      'execute graphql requests',
      'bypass graphql field security',
    ];
  }

  /**
   * Bypass user access.
   *
   * @before
   */
  protected function injectAccount() {
    // Replace the current user with a prophecy that has the defined
    // permissions.
    $user = $this->prophesize(AccountProxyInterface::class);

    $user->hasPermission(Argument::that(function ($arg) {
      return in_array($arg, $this->userPermissions());
    }))->willReturn(AccessResult::allowed());

    $user->id()->willReturn(0);
    $user->isAnonymous()->willReturn(TRUE);
    $user->isAuthenticated()->willReturn(FALSE);
    $this->accountProphecy = $user;
    $this->container->set('current_user', $user->reveal());
  }
}