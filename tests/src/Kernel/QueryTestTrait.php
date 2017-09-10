<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

/**
 * Common methods for GraphQL query tests.
 */
trait QueryTestTrait {

  /**
   * Issue a simple query without caring about the result.
   *
   * @param $query
   *   The query string.
   * @param array $variables
   *   Query variables.
   */
  protected function query($query, $variables = []) {
    $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'query' => $query,
      'variables' => $variables,
    ]));
  }

  /**
   * Issue a persisted query.
   *
   * @param $id
   *   The query id.
   * @param $version
   *   The query map version.
   * @param array $variables
   *   Query variables.
   */
  protected function persistedQuery($id, $version, $variables = []) {
    $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'id' => $id,
      'version' => $version,
      'variables' => $variables,
    ]));
  }

  /**
   * Simulate batched queries.
   *
   * @param $queries
   *   A set of queries to be executed in one go.
   */
  protected function batchedQueries($queries) {
    $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [], [], [], [], json_encode($queries)));
  }

  /**
   * Enable caching in CLI environments.
   */
  protected function enableCliCache() {
    // Disable the cli deny policy because we actually want caching on cli
    // when kernel testing it.
    $cliPolicy = $this->prophesize(RequestPolicyInterface::class);
    $cliPolicy->check(Argument::cetera())->willReturn(NULL);
    $this->container->set('graphql.request_policy.deny_command_line', $cliPolicy->reveal());
  }

  /**
   * Bypass user access.
   */
  protected function byPassAccess() {
    // Replace the current user with one that is allowed to do GraphQL requests.
    $user = $this->prophesize(AccountProxyInterface::class);
    $user->hasPermission('execute graphql requests')
      ->willReturn(AccessResult::allowed());
    $user->id()->willReturn(0);
    $user->isAnonymous()->willReturn(TRUE);
    $this->container->set('current_user', $user->reveal());
  }
}
