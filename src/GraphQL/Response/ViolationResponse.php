<?php

declare(strict_types = 1);

namespace Drupal\graphql\GraphQL\Response;

use Drupal\graphql\GraphQL\Violation\ViolationCollection;

/**
 * Base class for responses containing the violations.
 */
class ViolationResponse implements ResponseInterface {

  /**
   * Violations.
   *
   * @var \Drupal\graphql\GraphQL\Violation\ViolationCollection
   */
  protected $violations;

  /**
   * ViolationResponse constructor.
   *
   * @param \Drupal\graphql\GraphQL\Violation\ViolationCollection|null $violations
   *   List of violations occurred during query or mutation.
   */
  public function __construct(?ViolationCollection $violations = NULL) {
    $this->violations = $violations ?: new ViolationCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function code(): int {
    return 200;
  }

  /**
   * Gets the violations.
   *
   * @return array
   *   List of violations occurred during query or mutation.
   */
  public function errors(): array {
    return $this->violations->getViolations();
  }

}
