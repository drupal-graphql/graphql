<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
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
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $repository;

  /**
   * FieldContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   */
  public function __construct(ResolveContext $context, ResolveInfo $info) {
    $this->context = $context;
    $this->info = $info;
  }

  /**
   * @param callable $callable
   *
   * @return mixed
   */
  public function executeInContext(callable $callable) {
    $repository = $this->context->getContextRepository();
    return $repository->executeInContext($this->context, $this->info->path, $callable);
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
    $repository = $this->context->getContextRepository();
    $repository->setContextValue($this->context, $this->info->path, $name, $value);
    return $this;
  }

  /**
   * @param $name
   * @param $default
   *
   * @return mixed
   */
  public function getContextValue($name, $default) {
    $repository = $this->context->getContextRepository();
    return $repository->getContextValue($this->context, $this->info->path, $name, $default);
  }
}
