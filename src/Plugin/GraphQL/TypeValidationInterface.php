<?php

namespace Drupal\graphql\Plugin\GraphQL;

/**
 * Interface for type plugins that intend to employ their own validation rules.
 */
interface TypeValidationInterface {

  /**
   * Validate if a certain value matches this type.
   *
   * @param mixed $value
   *   The value to be checked.
   *
   * @return bool
   *   A boolean value indicating if the value conforms to this type.
   */
  public function isValidValue($value);
}