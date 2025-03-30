<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;

/**
 * Helper class for dealing with deferred promises.
 */
class DeferredUtility {

  /**
   * The promise adapter.
   */
  public static ?SyncPromiseAdapter $promiseAdapter = NULL;

  /**
   * Return the singleton promise adapter.
   *
   * @return \GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter
   *   The singleton promise adapter.
   */
  public static function promiseAdapter(): SyncPromiseAdapter {
    if (!isset(static::$promiseAdapter)) {
      static::$promiseAdapter = new SyncPromiseAdapter();
    }

    return static::$promiseAdapter;
  }

  /**
   * Execute a callback after a value is resolved.
   */
  public static function applyFinally(mixed $value, callable $callback): mixed {
    if ($value instanceof SyncPromise) {
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
   * Execute a callback after a value is resolved and return the result.
   */
  public static function returnFinally(mixed $value, callable $callback): mixed {
    if ($value instanceof SyncPromise) {
      return $value->then(function ($value) use ($callback) {
        return $callback($value);
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
  public static function waitAll(array $values): Deferred|array {
    if (static::containsDeferred($values)) {
      return new Deferred(function () use ($values) {
        $adapter = static::promiseAdapter();
        return $adapter->all(array_map(function ($value) use ($adapter) {
          if ($value instanceof SyncPromise) {
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
  public static function containsDeferred(array $values): bool {
    foreach ($values as $value) {
      if ($value instanceof SyncPromise) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
