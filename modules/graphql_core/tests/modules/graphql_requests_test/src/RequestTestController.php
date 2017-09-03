<?php

namespace Drupal\graphql_requests_test;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RequestTestController {

  /**
   * A simple test controller.
   */
  public function test() {
    return ['#markup' => '<p>Test</p>'];
  }

  /**
   * A redirect test controller.
   */
  public function redirect() {
    return new RedirectResponse('/graphql-request/test');
  }
}