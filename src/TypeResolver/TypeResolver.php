<?php

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Attempts to GraphQL type definitions from arbitrary data types.
 */
class TypeResolver implements TypeResolverWithRelaySupportInterface {
  /**
   * Unsorted list of type resolvers nested and keyed by priority.
   *
   * @var \Drupal\graphql\TypeResolver\TypeResolverInterface[]
   */
  protected $resolvers;

  /**
   * Sorted list of type resolvers.
   *
   * @var \Drupal\graphql\TypeResolver\TypeResolverInterface[]
   */
  protected $sortedResolvers;

  /**
   * {@inheritdoc}
   */
  public function applies(DataDefinitionInterface $definition) {
    return TRUE;
  }

  /**
   * Adds a active theme negotiation service.
   *
   * @param \Drupal\graphql\TypeResolver\TypeResolverInterface $resolver
   *   The type resolver to add.
   * @param int $priority
   *   Priority of the type resolver.
   */
  public function addTypeResolver(TypeResolverInterface $resolver, $priority = 0) {
    $this->resolvers[$priority][] = $resolver;
    $this->sortedResolvers = NULL;
  }

  /**
   * Returns the sorted array of type resolvers.
   *
   * @return \Drupal\graphql\TypeResolver\TypeResolverInterface[]
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
  public function resolveRecursive(DataDefinitionInterface $type) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver->applies($type)) {
        return $resolver->resolveRecursive($type);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function collectTypes() {
    return call_user_func_array('array_merge', array_map(function (TypeResolverInterface $resolver) {
      return $resolver->collectTypes();
    }, $this->getSortedResolvers()));
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayNode($type, $id) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayNode($type, $id)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayNode($type, $id) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayNode($type, $id)) {
        return $resolver->resolveRelayNode($type, $id);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayType($object) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayType($object)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayType($object) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayType($object)) {
        return $resolver->resolveRelayType($object);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayGlobalId($type, $value) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayGlobalId($type, $value)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayGlobalId($type, $value) {
    foreach ($this->getSortedResolvers() as $resolver) {
      if ($resolver instanceof TypeResolverWithRelaySupportInterface && $resolver->canResolveRelayGlobalId($type, $value)) {
        return $resolver->resolveRelayGlobalId($type, $value);
      }
    }

    return FALSE;
  }
}
