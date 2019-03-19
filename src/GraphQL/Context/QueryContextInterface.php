<?php

namespace Drupal\graphql\GraphQL\Context;

use Drupal\graphql\GraphQL\Execution\ResolveContext;

/**
 * Execute logic in context specific to a given query path.
 */
interface QueryContextInterface {

  /**
   * Override a context value for a given query path.
   *
   * @param \Drupal\graphql\GraphQL\Context\ResolveContext $context
   * @param array $path
   *   The field path as an array.
   * @param $id
   * @param $value
   *   The context value.
   *
   * @return void
   */
  public function overrideContext(ResolveContext $context, array $path, $id, $value);

  /**
   * Execute a callable in the context registered for a given path.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param array $path
   *   The field path as an array.
   * @param callable $callable
   *   The callable to invoke within the context.
   *
   * @return mixed
   *   The value returned from the callable.
   */
  public function executeInContext(ResolveContext $context, array $path, callable $callable);
}
