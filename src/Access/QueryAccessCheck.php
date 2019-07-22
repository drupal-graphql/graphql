<?php

namespace Drupal\graphql\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\Entity\ServerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class QueryAccessCheck implements AccessInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * QueryAccessCheck constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

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
    // If the user has the global permission to execute any query, let them.
    if ($account->hasPermission("execute $id arbitrary graphql requests")) {
      return AccessResult::allowed();
    }

    $request = $this->requestStack->getCurrentRequest();
    /** @var \GraphQL\Server\OperationParams[] $operations */
    if (!$operations = $request->attributes->get('operations', [])) {
      return AccessResult::forbidden();
    }

    $operations = is_array($operations) ? $operations : [$operations];
    foreach ($operations as $operation) {
      // If a query was provided by the user, this is an arbitrary query (it's
      // not a persisted query). Hence, we only grant access if the user has the
      // permission to execute any query.
      if ($operation->getOriginalInput('query')) {
        return AccessResult::allowedIfHasPermission($account, "execute $id arbitrary graphql requests");
      }
    }

    // If we reach this point, this is a persisted query.
    return AccessResult::allowedIfHasPermission($account, "execute $id persisted graphql requests");
  }

}
