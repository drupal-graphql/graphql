<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Context that is passed to data producer plugins.
 */
class FieldContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * FieldContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context that has been passed down.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   Schema type information of the current field.
   */
  public function __construct(
    protected ResolveContext $context,
    protected ResolveInfo $info,
  ) {
    $this->addCacheContexts(['user.permissions']);
  }

  /**
   * Returns the current field name.
   */
  public function getFieldName(): string {
    return $this->info->fieldName;
  }

  /**
   * Returns the language set as context.
   */
  public function getContextLanguage(): ?string {
    return $this->context->getContextLanguage();
  }

  /**
   * Sets the context language.
   *
   * @return $this
   */
  public function setContextLanguage(string $language) {
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
   * @param mixed $value
   *   The value of the context.
   *
   * @return $this
   */
  public function setContextValue(string $name, mixed $value) {
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
  public function getContextValue(string $name): mixed {
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
   * @return bool
   *   TRUE if the context exists, FALSE Otherwise.
   */
  public function hasContextValue(string $name): bool {
    return $this->context->hasContextValue($this->info, $name);
  }

}
