<?php

declare(strict_types = 1);

namespace Drupal\graphql\GraphQL\Violation;

/**
 * A collection of violations that can be triggered by a GraphQL mutation.
 */
interface ViolationCollectionInterface {

  /**
   * Adds the violation.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   Violation message.
   * @param array $properties
   *   Other properties related to the violation.
   */
  public function addViolation($message, array $properties = []): void;

  /**
   * Adds multiple violations.
   *
   * @param string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[] $messages
   *   Violation messages.
   * @param array $properties
   *   Other properties related to the violation.
   */
  public function addViolations(array $messages, array $properties = []): void;

  /**
   * Gets the violations.
   *
   * @return array
   *   Violations.
   */
  public function getViolations(): array;

}
