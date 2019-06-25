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
    $schema = <<<GQL
      type Query {
        root: String
      }
GQL;
    $this->setUpSchema($schema);
    $this->container->get('http_kernel')->handle(Request::create('/graphql/graphql_test', 'POST', [], [], [], [], '{ invalid'));
  }

}
