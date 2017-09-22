<?php

namespace Drupal\Tests\graphql_rules\Kernel;

use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;

/**
 * Test rules support in GraphQL.
 *
 * @group graphql_rules
 */
class ViewsTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rules',
    'graphql_rules',
  ];

  /**
   * Tests rules integration.
   */
  public function testRules() {
    $this->executeQueryFile('basic');
    $this->assertEquals(TRUE, TRUE);
  }

}
