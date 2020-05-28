<?php

namespace Drupal\graphql\GraphQL\Wrappers\Response;

/**
 * Response interface used for GraphQL responses.
 */
interface ResponseInterface {

  /**
   * The HTTP response code.
   *
   * @return int
   *   The HTTP status code, for example 200.
   */
  public function code(): int;

}
