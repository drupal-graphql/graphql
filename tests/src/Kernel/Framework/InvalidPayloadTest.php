<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Invalid payloads should not trigger a PHP error, but be handled as empty.
 *
 * @group graphql
 */
class InvalidPayloadTest extends GraphQLTestBase {

  public function testEmptyPayload() {
    /** @var \Symfony\Component\HttpFoundation\Response $result */
    $result = $this->container->get('http_kernel')
      ->handle(Request::create('/graphql', 'POST', [], [], [], [], '{ invalid'));
    $this->assertJson($result->getContent(), json_encode([
      'errors' => [
        'message' => "GraphQL Request must include at least one of those two parameters: \u0022query\u0022 or \u0022queryId\u0022\"",
        'category' => "request"
      ]
    ]));
  }

}
