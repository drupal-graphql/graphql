<?php

namespace Drupal\graphql\TypeResolver;

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
   * Allows registering of additional types that are not directly referenced.
   *
   * If an object- or union type is not explicitly referenced in the schema,
   * e.g. in case of only registering its interface type, this method allows the
   * type to be manually registered in the types list.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface[]
   *   An array of types to be manually registered with the schema.
   */
  public function collectTypes();

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
