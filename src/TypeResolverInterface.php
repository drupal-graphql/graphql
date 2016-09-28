<?php

namespace Drupal\graphql;

use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Provides a common interface for type resolvers.
 */
interface TypeResolverInterface {
  /**
   * Resolves the provided data definition recursively.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition to be resolved.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The resolved type.
   */
  public function resolveRecursive(DataDefinitionInterface $definition);

  /**
   * Determines if this implementation applies for the given data definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *
   * @return bool
   *   TRUE if this type resolver implementation can resolve the given data
   *   definition, FALSE otherwise.
   */
  public function applies(DataDefinitionInterface $definition);
}
