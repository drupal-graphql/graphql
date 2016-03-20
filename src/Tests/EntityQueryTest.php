<?php

/**
 * @file
 * Contains \Drupal\graphql\Tests\EntityQueryTest.
 */

namespace Drupal\graphql\Tests;

/**
 * Test fetching lists of entities through entity queries.
 *
 * @group graphql
 */
class EntityQueryTest extends QueryTestBase {
  /**
   * Helper function to issue a HTTP request with simpletest's cURL.
   *
   *
   * @return string
   *   The content returned from the request.
   */
  public function testSomething() {
    $query = '{
      entityQuery {
        node(nid: 1) {
          title
        }
      }
    }';

    $response = $this->query($query);
  }
}
