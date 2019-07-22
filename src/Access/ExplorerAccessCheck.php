<?php

namespace Drupal\graphql\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\Entity\ServerInterface;

class ExplorerAccessCheck implements AccessInterface {

  /**
   * Checks access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\graphql\Entity\ServerInterface $graphql_server
   *   The server instance.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, ServerInterface $graphql_server) {
    if ($account->hasPermission('bypass graphql access')) {
      return AccessResult::allowed();
    }

    $id = $graphql_server->id();
    return AccessResult::allowedIfHasPermissions($account, [
      "use $id graphql explorer",
      "execute $id arbitrary graphql requests",
    ]);
  }

}
