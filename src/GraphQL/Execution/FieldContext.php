<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
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
   * @return string
   */
  public function getContextLanguage() {
    return $this->context->getContextLanguage();
  }

  /**
   * @param $language
   *
   * @return $this
   */
  public function setContextLanguage($language) {
    $this->context->setContextLanguage($language);
    return $this;
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
    $this->context->setContextValue($this->info, $name, $value);
    return $this;
  }

  /**
   * Get a contextual value for the current field.
   *
   * Allows field resolvers to inherit contextual values from their ancestors.
   *
   * @param string $name
   *   The name of the context.
   *
   * @return mixed
   *   The current value of the given context or the given default value if the
   *   context wasn't set.
   */
  public function getContextValue($name) {
    return $this->context->getContextValue($this->info, $name);
  }

  /**
   * Checks whether contextual value for the current field exists.
   *
   * Also checks ancestors of the field.
   *
   * @param string $name
   *   The name of the context.
   *
   * @return boolean
   *   TRUE if the context exists, FALSE Otherwise.
   */
  public function hasContextValue($name) {
    return $this->context->hasContextValue($this->info, $name);
  }
}
