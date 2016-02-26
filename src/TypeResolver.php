<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver.
 */

namespace Drupal\graphql;

/**
 * Attempts to GraphQL type definitions from arbitrary data types.
 */
class TypeResolver implements TypeResolverInterface {
  /**
   * Unsorted list of type resolvers nested and keyed by priority.
   *
   * @var array
   */
  protected $resolvers;

  /**
   * Sorted list of type resolvers.
   *
   * @var array
   */
  protected $sortedResolvers;

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return TRUE;
  }

  /**
   * Adds a active theme negotiation service.
   *
   * @param \Drupal\graphql\TypeResolverInterface $resolver
   *   The type resolver to add.
   * @param int $priority
   *   Priority of the type resolver.
   */
  public function addTypeResolver(TypeResolverInterface $resolver, $priority = 0) {
    $this->resolvers[$priority][] = $resolver;
    $this->sortedResolvers = NULL;
  }

  /**
   * Returns the first applicable provider for the given type definition.
   *
   * @param mixed $type
   *
   * @return \Drupal\graphql\TypeResolverInterface|null
   */
  protected function getFirstApplicableTypeResolver($type) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver->applies($type)) {
        return $resolver;
      }
    }

    return NULL;
  }

  /**
   * Returns the sorted array of type resolvers.
   *
   * @return \Drupal\graphql\TypeResolverInterface[]
   *   An array of type resolver objects.
   */
  protected function getSortedResolvers() {
    if (!isset($this->sortedResolvers)) {
      krsort($this->resolvers);

      $this->sortedResolvers = [];
      foreach ($this->resolvers as $resolvers) {
        $this->sortedResolvers = array_merge($this->sortedResolvers, $resolvers);
      }
    }

    return $this->sortedResolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($resolver = $this->getFirstApplicableTypeResolver($type)) {
      return $resolver->resolveRecursive($type);
    }

    return NULL;
  }
}
