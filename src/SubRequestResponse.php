<?php

namespace Drupal\graphql;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\HttpFoundation\Response;

class SubRequestResponse extends Response implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * The request result.
   *
   * @var mixed
   */
  protected $result;

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
  public function __construct($result, $status = 200, array $headers = []) {
    parent::__construct('', $status, $headers);
    $this->result = $result;
  }

  /**
   * Gets the request result.
   *
   * @return mixed
   *   The request result.
   */
  public function getResult() {
    return $this->result;
  }

}