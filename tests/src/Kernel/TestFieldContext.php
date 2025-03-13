<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Helper class for mocking a field context during tests.
 */
class TestFieldContext extends FieldContext {

  /**
   * Empty constructor override, we don't need it.
   */
  public function __construct() {}

}
