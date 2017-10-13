<?php

namespace Drupal\graphql\Plugin\GraphQL;

/**
 * Retrieve GraphQL plugins.
 *
 * Stores and retrieves GraphQL schema plugins. Types, Interfaces, Fields
 * and Mutations.
 */
interface PluggableSchemaManagerInterface {

  /**
   * Search for a specific plugin.
   *
   * @param callable $selector
   *   A selector callable that will be used to array_filter the list of
   *   plugin definitions.
   * @param integer[] $types
   *   A list of type constants.
   * @param bool $invert
   *   Invert the selector result.
   *
   * @return object[]
   *   The list of matching plugin instances, keyed by name.
   */
  public function find(callable $selector, array $types, $invert = FALSE);

  /**
   * Search for a specific plugin.
   *
   * @param string $name
   *   The specific plugin name.
   * @param integer[] $types
   *   A list of type constants.
   *
   * @return object
   *   The highest weighted plugin with a specific name.
   */
  public function findByName($name, array $types);

  /**
   * Find the matching GraphQL data type for a Drupal type data identifier.
   *
   * Respects type chains. `entity:node:article` should return the
   * `NodeArticle` type if it is exposed or fall back to either `Node` or even
   * `Entity` otherwise.
   *
   * @param string $dataType
   *   The typed data identifier. E.g. `string` or `entity:node:article`.
   * @param string[] $types
   *   A list of type constants.
   *
   * @return object
   *   The matching type with the highest weight.
   */
  public function findByDataType($dataType, array $types = [
    GRAPHQL_UNION_TYPE_PLUGIN,
    GRAPHQL_TYPE_PLUGIN,
    GRAPHQL_INTERFACE_PLUGIN,
    GRAPHQL_SCALAR_PLUGIN,
  ]);

  /**
   * Retrieve all mutations.
   *
   * @return object[]
   *   The list of mutation plugins.
   */
  public function getMutations();

  /**
   * Retrieve all fields that are not associated with a specific type.
   *
   * @return object[]
   *   The list root field plugins.
   */
  public function getRootFields();

}
