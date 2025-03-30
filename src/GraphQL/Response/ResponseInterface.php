<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Response;

use Drupal\Component\Render\MarkupInterface;

/**
 * Response interface used for GraphQL responses.
 */
interface ResponseInterface {

  /**
   * Adds the violation.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   Violation message.
   * @param array $properties
   *   Other properties related to the violation.
   */
  public function addViolation(string|MarkupInterface $message, array $properties = []): void;

  /**
   * Adds multiple violations.
   *
   * @param array<string>|array<\Drupal\Component\Render\MarkupInterface> $messages
   *   Violation messages.
   * @param array $properties
   *   Other properties related to the violation.
   */
  public function addViolations(array $messages, array $properties = []): void;

  /**
   * Gets the violations.
   *
   * @return array<string>|array<\Drupal\Component\Render\MarkupInterface>
   *   Violations.
   */
  public function getViolations(): array;

  /**
   * Adds the violations from another response to this response.
   */
  public function mergeViolations(ResponseInterface $source): void;

}
