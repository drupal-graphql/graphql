<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Invalid payloads should not trigger a PHP error, but be handled as empty.
 */
class InvalidPayloadTest extends GraphQLTestBase {

  public function testEmptyPayload() {
    $this->container->get('http_kernel')->handle(Request::create('/graphql', 'POST', [], [], [], [], '{ invalid'));
  }

}
