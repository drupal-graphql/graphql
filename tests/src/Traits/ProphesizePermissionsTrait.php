<?php

namespace Drupal\Tests\graphql\Traits;

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
   * Set the prophesized roles.
   *
   * @return string[]
   */
  protected function userRoles() {
    return ['anonymous'];
  }

  /**
   * Bypass user access.
   */
  protected function injectAccount() {
    // Replace the current user with a prophecy that has the defined
    // permissions.
    $user = $this->prophesize(AccountProxyInterface::class);
    $permissions = $this->userPermissions();

    $user
      ->hasPermission(Argument::type('string'), Argument::cetera())
      ->will(function ($args) use ($permissions) {
        return in_array($args[0], $permissions);
      });

    $user
      ->getRoles(Argument::any())
      ->willReturn($this->userRoles());

    $user->id()->willReturn(0);
    $user->isAnonymous()->willReturn(TRUE);
    $user->isAuthenticated()->willReturn(FALSE);
    $this->accountProphecy = $user;
    $this->container->set('current_user', $user->reveal());
  }
}
