<?php

namespace Drupal\graphql\Plugin\GraphQL;

interface PluggableSchemaBuilderInterface {

  /**
   * Creates a type system plugin instance for a given plugin manager.
   *
   * @param string $pluginType
   *   The plugin type.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginConfiguration
   *   The plugin configuration.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The created plugin instance.
   */
  public function getInstance($pluginType, $pluginId, array $pluginConfiguration = []);

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
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of matching plugin instances, keyed by name.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
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
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The highest weighted plugin with a specific name.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
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
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The matching type with the highest weight.
   */
  public function findByDataType($dataType, array $types = [
    GRAPHQL_UNION_TYPE_PLUGIN,
    GRAPHQL_TYPE_PLUGIN,
    GRAPHQL_INTERFACE_PLUGIN,
    GRAPHQL_SCALAR_PLUGIN,
  ]);

}
