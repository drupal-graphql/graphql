<?php

declare(strict_types=1);

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

  /**
   * {@inheritdoc}
   */
  public function getContextValue($name): mixed {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setContextValue($name, $value) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasContextValue($name): bool {
    return FALSE;
  }

}
