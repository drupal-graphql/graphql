<?php

namespace Drupal\graphql\Utility;

class StringHelper {

  /**
   * Turn a list of machine names into a camel-cased string.
   *
   * @return string
   *   A camel-cased concatenation of the input components.
   *
   * @throws \InvalidArgumentException
   *   If the provided input does can't be converted to a specification compliant
   *   string representation for field or type names.
   */
  public static function camelCase() {
    $components = func_get_args();
    $string = is_array($components) ? implode('_', $components) : $components;
    $filtered = preg_replace('/^[^_a-zA-Z]+/', '', $string);
    $components = array_filter(preg_split('/[^a-zA-Z0-9]/', $filtered));

    if (!count($components)) {
      throw new \InvalidArgumentException(sprintf("Failed to create a specification compliant string representation for '%s'.", $string));
    }

    return implode('', array_map('ucfirst', $components));
  }

  /**
   * Turn a list of machine names into a property-cased string.
   *
   * @return string
   *   A camel-cased concatenation of the input components.
   */
  public static function propCase() {
    $result = call_user_func_array([static::class, 'camelCase'], func_get_args());
    return ctype_upper($result) ? strtolower($result) : lcfirst($result);
  }

}