<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Type\Definition\ResolveInfo;

class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $registry;

  /**
   * @var array
   */
  protected $contexts;

  /**
   * @var
   */
  protected $operation;

  /**
   * @var bool
   */
  protected $caching;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param bool $caching
   */
  public function __construct(ResolverRegistryInterface $registry, $caching = TRUE) {
    $this->registry = $registry;
    $this->caching = $caching;
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  public function getRegistry() {
    return $this->registry;
  }

  /**
   * @return bool
   */
  public function useCaching() {
    return $this->caching;
  }

  /**
   * Sets a contextual value for the current field and its descendants.
   *
   * Allows field resolvers to set contextual values which can be inherited by
   * their descendants.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   * @param $value
   *   The value of the context.
   *
   * @return $this
   */
  public function setContextValue(ResolveInfo $info, $name, $value) {
    $key = implode('.', $info->path);
    $this->contexts[$key][$name] = $value;

    return $this;
  }

  /**
   * Get a contextual value for the current field.
   *
   * Allows field resolvers to inherit contextual values from their ancestors.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   *
   * @return mixed
   *   The current value of the given context or NULL if it's not set.
   */
  public function getContextValue(ResolveInfo $info, $name) {
    $path = $info->path;

    do {
      $key = implode('.', $path);
      if (isset($this->contexts[$key]) && array_key_exists($name, $this->contexts[$key])) {
        return $this->contexts[$key][$name];
      }

      array_pop($path);
    } while (count($path));

    return NULL;
  }

  /**
   * Checks whether contextual value for the current field exists.
   *
   * Also checks ancestors of the field.
   *
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   * @param string $name
   *   The name of the context.
   *
   * @return boolean
   *   TRUE if the context exists, FALSE Otherwise.
   */
  public function hasContextValue(ResolveInfo $info, $name) {
    $path = $info->path;

    do {
      $key = implode('.', $path);
      if (isset($this->contexts[$key]) && array_key_exists($name, $this->contexts[$key])) {
        return TRUE;
      }

      array_pop($path);
    } while (count($path));

    return FALSE;
  }
}
