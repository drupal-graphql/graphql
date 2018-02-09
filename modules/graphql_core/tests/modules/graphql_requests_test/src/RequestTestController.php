<?php

namespace Drupal\graphql_requests_test;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RequestTestController {

  /**
   * A simple test controller.
   */
  public function test() {
    return new Response('<p>Test</p>');
  }

  /**
   * A redirect test controller.
   */
  public function redirect() {
    return new RedirectResponse('/graphql-request/test');
  }
}