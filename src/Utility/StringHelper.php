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
    $args = func_get_args();
    $components = array_map(function ($component) {
      return preg_replace('/[^a-zA-Z0-9_]/', '_', $component);
    }, $args);

    $components = array_filter(explode('_', implode('_', $components)));
    if (!count($components)) {
      throw new \InvalidArgumentException(sprintf("Failed to create a specification compliant string representation for '%s'.", implode('', $args)));
    }

    $string = implode('', array_map('ucfirst', $components));
    $string = $string && is_numeric($string[0]) ? "_$string" : $string;
    return $string;
  }

  /**
   * Turn a list of machine names into a upper-cased string.
   *
   * @return string
   *   A upper-cased concatenation of the input components.
   */
  public static function upperCase() {
    $result = call_user_func_array([static::class, 'camelCase'], func_get_args());
    return strtoupper($result);
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

  /**
   * Wraps a type string in brackets declaring it as a list.
   *
   * @param string $type
   *   The type to declare as a list.
   *
   * @return string
   *   The decorated type string.
   */
  public static function listType($type) {
    return "[$type]";
  }

  /**
   * Appends an exclamation mark to a type string declaring it as non-null.
   *
   * @param string $type
   *   The type to declare as non-null.
   *
   * @return string
   *   The decorated type string.
   */
  public static function nonNullType($type) {
    return "$type!";
  }

  /**
   * Decorates a type as non-null and/or as a list.
   *
   * @param string $type
   *   The type to declare as non-null.
   * @param bool $list
   *   Whether to mark the type as a list.
   * @param bool $required
   *   Whether to mark the type as required.
   *
   * @return string
   *   The decorated type.
   */
  public static function decorateType($type, $list = FALSE, $required = FALSE) {
    if (!empty($list)) {
      $type = static::listType($type);
    }

    if (!empty($required)) {
      $type = static::nonNullType($type);
    }

    return $type;
  }

  /**
   * Parses a type definition from a string and properly decorates it.
   *
   * Converts type strings (e.g. [Foo!]) to their object representations.
   *
   * @param string $type
   *   The type string to parse.
   *
   * @return array
   *   The extracted type with the type decorators.
   */
  public static function parseType($type) {
    $decorators = [];
    $unwrapped = $type;
    $matches = NULL;

    while (preg_match('/[\[\]!]/', $unwrapped) && preg_match_all('/^(\[?)(.*?)(\]?)(!*?)$/', $unwrapped, $matches)) {
      if ($unwrapped === $matches[2][0] || empty($matches[1][0]) !== empty($matches[3][0])) {
        throw new \InvalidArgumentException(sprintf("Invalid type declaration '%s'.", $type));
      }

      if (!empty($matches[4][0])) {
        array_unshift($decorators, ['GraphQL\Type\Definition\Type', 'nonNull']);
      }

      if (!empty($matches[1][0]) && !empty($matches[3][0])) {
        array_unshift($decorators, ['GraphQL\Type\Definition\Type', 'listOf']);
      }

      $unwrapped = $matches[2][0];
    }

    return [$unwrapped, $decorators];
  }

}