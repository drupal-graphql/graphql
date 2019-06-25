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

  protected function setUp() {
    parent::setUp();

    $schema = <<<GQL
      type Query {
        root: String
      }
GQL;

    $this->setUpSchema($schema);
  }

  public function testEmptyPayload() {
    $request = Request::create('/graphql/test', 'POST', [], [], [], [], '{ invalid');
    $this->container->get('http_kernel')->handle($request);
  }

}
