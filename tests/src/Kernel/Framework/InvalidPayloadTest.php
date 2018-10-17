<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Invalid payloads should not trigger a PHP error, but be handled as empty.
 */
class InvalidPayloadTest extends GraphQLTestBase {

  public function testEmptyPayload() {
    $gql_schema = <<<GQL
      type Query {
        root: String
      }
GQL;
    $this->setUpSchema($gql_schema, 'graphql_test');
    $this->container->get('http_kernel')->handle(Request::create('/graphql/graphql_test', 'POST', [], [], [], [], '{ invalid'));
  }

}
