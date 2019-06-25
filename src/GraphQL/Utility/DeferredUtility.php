<?php

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;

class DeferredUtility {

  /**
   * The promise adapter.
   *
   * @var \GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter
   */
  static $promiseAdapter;

  /**
   * Return the singleton promise adapter.
   *
   * @return \GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter
   *   The singleton promise adapter.
   */
  public static function promiseAdapter() {
    if (!isset(static::$promiseAdapter)) {
      static::$promiseAdapter = new SyncPromiseAdapter();
    }

    return static::$promiseAdapter;
  }

  /**
   * @param mixed $value
   * @param callable $callback
   *
   * @return mixed
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
   * @return \GraphQL\Deferred|mixed
   */
  public static function returnFinally($value, callable $callback) {
    if ($value instanceof Deferred) {
      return new Deferred(function () use ($value, $callback) {
        return $value->then(function ($value) use ($callback) {
          return $callback($value);
        });
      });
    }

    return $callback($value);
  }

  /**
   * Ensures that all promises in the given array are resolved.
   *
   * The input array may contain any combination of promise and non-promise
   * values. If it does not contain any promises at all, it will simply return
   * the original array unchanged.
   *
   * @param array $values
   *   An array of promises and arbitrary values.
   *
   * @return \GraphQL\Deferred|array
   *   The deferred result or the unchanged input array if it does not contain
   *   any promises.
   */
  public static function waitAll(array $values) {
    if (static::containsDeferred($values)) {
      return new Deferred(function () use ($values) {
        $adapter = static::promiseAdapter();
        return $adapter->all(array_map(function ($value) use ($adapter) {
          if ($value instanceof Deferred) {
            return $adapter->convertThenable($value);
          }

          return $value;
        }, $values));
      });
    }

    return $values;
  }

  /**
   * Checks if there are any deferred values in the given array.
   *
   * @param array $values
   *   The array to check for deferred values.
   *
   * @return bool
   *   TRUE if there are any deferred values in the given array.
   */
  public static function containsDeferred(array $values) {
    foreach ($values as $value) {
      if ($value instanceof Deferred) {
        return TRUE;
      }
    }

    return FALSE;
  }

}