<?php

declare(strict_types = 1);

namespace Drupal\graphql\GraphQL\Response;

/**
 * Response interface used for GraphQL responses.
 */
interface ResponseInterface {

  /**
   * Gets the violations.
   *
   * @return string[]
   *   List of violations occurred during query or mutation.
   */
  public function errors(): array;

}
