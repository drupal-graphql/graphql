<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\language\ConfigurableLanguageManagerInterface;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;

class FieldContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\GraphQL\Execution\ResolveContext
   */
  protected $context;

  /**
   * @var \GraphQL\Type\Definition\ResolveInfo
   */
  protected $info;

  /**
   * FieldContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   */
  public function __construct(ResolveContext $context, ResolveInfo $info) {
    $this->addCacheContexts(['user.permissions']);

    $this->context = $context;
    $this->info = $info;
  }

  /**
   * @param callable $callable
   *
   * @return mixed
   */
  public function executeInContext(callable $callable) {
    // TODO: Decorate with current contexts based on path.
    return $callable();
  }

  /**
   * @param callable $callable
   *
   * @return \GraphQL\Deferred
   */
  public function deferInContext(callable $callable) {
    return new Deferred(function () use ($callable) {
      return $this->executeInContext($callable);
    });
  }

  /**
   * Sets a contextual value for the current field and its descendants.
   *
   * Allows field resolvers to set contextual values which can be inherited by
   * their descendants.
   *
   * @param string $name
   *   The name of the context.
   * @param $value
   *   The value of the context.
   *
   * @return $this
   */
  public function setContextValue($name, $value) {
    // TODO: Set context value in context bag (path based).
    return $this;
  }

  /**
   * @param $name
   * @param $default
   *
   * @return mixed
   */
  public function getContextValue($name, $default) {
    // TODO: Get context value from context bag (path based).
    return NULL;
  }
}
