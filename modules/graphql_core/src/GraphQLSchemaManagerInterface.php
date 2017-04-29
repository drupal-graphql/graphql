<?php

namespace Drupal\graphql_core;

/**
 * Retrieve GraphQL plugins.
 *
 * Stores and retrieves GraphQL schema plugins. Types, Interfaces, Fields
 * and Mutations.
 */
interface GraphQLSchemaManagerInterface {

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
   * @return object[]
   *   The list of matching plugin instances, keyed by name.
   */
  public function findByName($name, array $types);

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
