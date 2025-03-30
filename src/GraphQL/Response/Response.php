<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Response;

use Drupal\Component\Render\MarkupInterface;

/**
 * Base class for responses containing the violations.
 */
class Response implements ResponseInterface {

  /**
   * List of violations.
   */
  protected array $violations = [];

  /**
   * {@inheritdoc}
   */
  public function addViolation(string|MarkupInterface $message, array $properties = []): void {
    $properties['message'] = (string) $message;
    $this->violations[] = $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addViolations(array $messages, array $properties = []): void {
    foreach ($messages as $message) {
      $this->addViolation($message, $properties);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getViolations(): array {
    return $this->violations;
  }

  /**
   * {@inheritdoc}
   */
  public function mergeViolations(ResponseInterface $source): void {
    $this->violations = array_merge($this->violations, $source->getViolations());
  }

}
