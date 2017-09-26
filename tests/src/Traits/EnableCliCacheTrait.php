<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Prophecy\Argument;

/**
 * Trait to automatically enable CLI caching in GraphQL tests.
 */
trait EnableCliCacheTrait {


  /**
   * Enable caching in CLI environments.
   *
   * @before
   */
  protected function enableCliCache() {
    // Disable the cli deny policy because we actually want caching on cli
    // when kernel testing it.
    $cliPolicy = $this->prophesize(RequestPolicyInterface::class);
    $cliPolicy->check(Argument::cetera())->willReturn(NULL);
    $this->container->set('graphql.request_policy.deny_command_line', $cliPolicy->reveal());
  }
}