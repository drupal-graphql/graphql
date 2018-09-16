<?php

namespace Drupal\graphql\GraphQL\Utility;

class JsonHelper {

  /**
   * Decode encoded values recursively.
   *
   * @param array $values
   *   The values to decode.
   *
   * @return array
   *   The decoded values.
   */
  public static function decodeParams(array $values = []) {
    return array_map(function($value) {
      if (!is_string($value)) {
        return $value;
      }

      if (($decoded = json_decode($value, TRUE)) !== NULL && $decoded != $value) {
        return is_array($decoded) ? static::decodeParams($decoded) : $decoded;
      }

      return $value;
    }, $values);
  }

}
