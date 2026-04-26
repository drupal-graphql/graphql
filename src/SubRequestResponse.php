<?php

declare(strict_types=1);

namespace Drupal\graphql;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Helper class that holds a request result for subrequests.
 */
class SubRequestResponse extends Response implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * SubrequestResponse constructor.
   *
   * @param mixed $result
   *   The request result.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   */
  public function __construct(
    protected mixed $result,
    int $status = 200,
    array $headers = [],
  ) {
    parent::__construct('', $status, $headers);
  }

  /**
   * Gets the request result.
   *
   * @return mixed
   *   The request result.
   */
  public function getResult(): mixed {
    return $this->result;
  }

}
