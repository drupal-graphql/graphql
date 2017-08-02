<?php

namespace Drupal\graphql_core;

use Symfony\Component\HttpFoundation\Response;

/**
 * A special response object used for collecting information from sub-requests.
 */
class SubrequestResponse extends Response {
  /**
   * Bag of information collected within the sub-request.
   *
   * @var array
   */
  protected $data;

  /**
   * Retrieve a specific value collected in a sub-request.
   *
   * @param string $key
   *   The key.
   * @param mixed $default
   *   A default value if the value is not available.
   *
   * @return mixed
   *   The sub request value.
   */
  public function get($key, $default = NULL) {
    return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
  }

  /**
   * SubrequestResponse constructor.
   *
   * @param array $data
   *   The collected data payload.
   */
  public function __construct(array $data) {
    $this->data = $data;
    parent::__construct('', 200, []);
  }

}
