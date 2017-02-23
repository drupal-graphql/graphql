<?php

namespace Drupal\graphql\GraphQL;

interface ValueWrapperInterface {

  /**
   * Set the wrapped value.
   *
   * @param mixed $value
   */
  public function setValue($value);

  /**
   * Get the wrapped value.
   *
   * @return mixed
   */
  public function getValue();

}