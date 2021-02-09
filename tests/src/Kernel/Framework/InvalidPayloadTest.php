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

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $schema = <<<GQL
      type Query {
        root: String
      }
GQL;

    $this->setUpSchema($schema);
  }

  /**
   * Tests the empty payload.
   */
  public function testEmptyPayload(): void {
    $request = Request::create('/graphql/test', 'POST', [], [], [], [], '{ invalid');
    $this->container->get('http_kernel')->handle($request);
  }

}
