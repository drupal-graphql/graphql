<?php

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Deferred;

class DeferredUtility {

  /**
   * @param mixed $value
   * @param callable $callback
   *
   * @return \GraphQL\Deferred
   */
  public static function applyFinally($value, callable $callback) {
    if ($value instanceof Deferred) {
      // Recursively apply this function to deferred results.
      $value->then(function ($inner) use ($callback) {
        return static::applyFinally($inner, $callback);
      });
    }
    else {
      $callback($value);
    }

    return $value;
  }

  /**
   * @param mixed $value
   * @param callable $callback
   *
   * @return \GraphQL\Deferred
   */
  public static function returnFinally($value, callable $callback) {
    if ($value instanceof Deferred) {
      // Recursively apply this function to deferred results.
      return new Deferred(function () use ($value, $callback) {
        return $value->then(function ($value) use ($callback) {
          return $callback($value);
        });
      });
    }

    return $callback($value);
  }

}