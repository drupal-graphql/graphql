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
   * @param string[] $types
   *   A list of type constants.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of matching plugin instances, keyed by name.
   */
  public function find(callable $selector, array $types);

  /**
   * Search for a specific plugin by data type or name.
   *
   * Data type matches are given precedence over name matches.
   *
   * @param string $input
   *   The specific plugin name or data type.
   * @param string[] $types
   *   A list of type constants.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The highest weighted plugin with the given name or data type or a dummy
   *   fallback type.
   */
  public function findByDataTypeOrName($input, array $types);

  /**
   * Search for a specific plugin.
   *
   * @param string $name
   *   The specific plugin name.
   * @param string[] $types
   *   A list of type constants.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The highest weighted plugin with the given name.
   */
  public function findByName($name, array $types);

  /**
   * Search for a specific plugin by its data type.
   *
   * @param string $type
   *   The specific plugin data type.
   * @param string[] $types
   *   A list of type constants.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The highest weighted plugin with the given data type.
   */
  public function findByDataType($type, array $types);

}
