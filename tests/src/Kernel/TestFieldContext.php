<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Helper class for mocking a field context during tests.
 */
class TestFieldContext extends FieldContext {

  /**
   * Dummy constructor, we don't need the full setup.
   *
   * @param string|null $testLanguage
   *   Helper property during tests. The language that should be returned by the
   *   getContextLanguage() method.
   */
  public function __construct(protected ?string $testLanguage = NULL) {
  }

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

  /**
   * {@inheritdoc}
   */
  public function getContextLanguage(): ?string {
    return $this->testLanguage;
  }

}
