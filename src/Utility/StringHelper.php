<?php

namespace Drupal\graphql\Utility;

/**
 * String utilities to help in generting a GraphQL schema.
 */
class StringHelper {
  /**
   * Formats and filters a string as a camel-cased type name.
   *
   * Strips out any non-alphanumeric characters and turns it into a camel-cased
   * string.
   *
   * @param string $string
   *   The string to be formatted.
   *
   * @return string
   *   The formatted string.
   */
  public static function formatTypeName($string) {
    // I know words. I have the best words. (© Donald Trump)
    $words = preg_split('/[^a-zA-Z0-9]/', strtolower($string));
    return implode('', array_map('ucfirst', array_map('trim', $words)));
  }

  /**
   * Formats a list of property names and ensures unambiguousness.
   *
   * Returns the list in its original order.
   *
   * @param array $strings
   *   Array of strings to format.
   * @param array $others
   *   An array of strings that should be taken into consideration.
   *
   * @return array The list of formatted strings.
   * The list of formatted strings.
   */
  public static function formatPropertyNameList(array $strings, array $others = []) {
    return array_reduce($strings, function (array $names, $input) use ($others) {
      $formatted = static::formatPropertyName($input);
      $formatted = static::ensureUnambiguousness($formatted, $names + $others);
      return $names + [$input => $formatted];
    }, []);
  }

  /**
   * Formats and filters a string as a camel-cased property name.
   *
   * Strips out any non-alphanumeric characters and turns it into a camel-cased
   * string. This may lead to ambiguous property names. Hence, you need to
   * ensure uniqueness yourself.
   *
   * @param string $string
   *   The string to be formatted.
   *
   * @return string
   *   The formatted string.
   */
  public static function formatPropertyName($string) {
    // I know words. I have the best words. (© Donald Trump)
    $words = preg_split('/[^a-zA-Z0-9]/', strtolower($string));
    return lcfirst(implode('', array_map('ucfirst', array_map('trim', $words))));
  }

  /**
   * Ensure that a string is unambiguous given a list of other strings.
   *
   * @param string $string
   *   The string to ensure unambiguousness for.
   * @param array $others
   *   An array of strings that should be taken into consideration.
   *
   * @return string
   *   The input string, potentially with a numerical suffix that ensures
   *   unambiguousness.
   */
  public static function ensureUnambiguousness($string, $others) {
    $suffix = '';
    while (in_array($string . $suffix, $others)) {
      $suffix = (string) (intval($suffix) + 1);
    }

    return $string . $suffix;
  }
}
