<?php

declare(strict_types = 1);

namespace Drupal\graphql\GraphQL\Wrappers\Response;

use Drupal\graphql\GraphQL\Wrappers\Violation\ViolationCollection;

/**
 * Base class for responses containing the violations.
 */
class ViolationResponse implements ResponseInterface {

  /**
   * Violations.
   *
   * @var \Drupal\graphql\GraphQL\Wrappers\Violation\ViolationCollection
   */
  protected $violations;

  /**
   * ViolationResponse constructor.
   *
   * @param \Drupal\graphql\GraphQL\Wrappers\Violation\ViolationCollection|null $violations
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
