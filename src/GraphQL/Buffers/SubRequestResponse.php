<?php

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\HttpFoundation\Response;

class SubRequestResponse extends Response implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * The request result.
   *
   * @var array
   */
  protected $result;

  /**
   * SubrequestResponse constructor.
   *
   * @param array $result
   *   The request result.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   */
  public function __construct(array $result, $status = 200, array $headers = []) {
    parent::__construct('', $status, $headers);
    $this->result = $result;
  }

  /**
   * Gets the request result.
   *
   * @return array
   *   The request result.
   */
  public function getResult() {
    return $this->result;
  }

}