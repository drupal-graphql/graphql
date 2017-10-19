<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\PluginManagerInterface;

class SchemaBuilder {

  /**
   * Static cache of type system plugin instances.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   */
  protected $instances = [];

  /**
   * Static cache of plugin definitions.
   *
   * @var array
   */
  protected $definitions;

  /**
   * List of registered type system plugin managers.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface[]
   */
  protected $pluginManagers;

  /**
   * SchemaBuilder constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface[] $pluginManagers
   *   List of type system plugin managers.
   */
  public function __construct(array $pluginManagers) {
    $this->pluginManagers = $pluginManagers;
  }

  /**
   * Returns the list of sorted plugin definitions.
   *
   * @return array
   *   The list of sorted plugin definitions.
   */
  protected function getDefinitions() {
    if ($this->definitions == NULL) {
      foreach ($this->pluginManagers as $manager) {
        foreach ($manager->getDefinitions() as $pluginId => $definition) {
          $this->definitions[] = [
            'id' => $pluginId,
            'type' => $definition['pluginType'],
            'weight' => $definition['weight'],
            'manager' => $manager,
            'definition' => $definition,
          ];
        }
      }

      uasort($this->definitions, '\Drupal\Component\Utility\SortArray::sortByWeightElement');
      $this->definitions = array_reverse($this->definitions);
    }

    return $this->definitions;
  }

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
  public function find(callable $selector, array $types, $invert = FALSE) {
    $instances = [];
    foreach ($this->getDefinitions() as $index => $definition) {
      $name = $definition['definition']['name'];
      if (empty($name)) {
        throw new InvalidPluginDefinitionException('Invalid GraphQL plugin definition. No name defined.');
      }

      if (!array_key_exists($name, $instances) && in_array($definition['definition']['pluginType'], $types)) {
        if ((($invert && !$selector($definition['definition'])) || $selector($definition['definition']))) {
          $instances[$name] = $this->getInstance($definition['manager'], $definition['type'], $definition['id']);
        }
      }
    }

    return $instances;
  }

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
  public function findByName($name, array $types) {
    $result = $this->find(function($definition) use ($name) {
      return $definition['name'] === $name;
    }, $types);

    if (empty($result)) {
      throw new InvalidPluginDefinitionException(sprintf('GraphQL plugin with name %s could not be found.', $name));
    }

    return array_pop($result);
  }

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
  ]) {
    $chain = explode(':', $dataType);

    while ($chain) {
      $dataType = implode(':', $chain);

      $types = $this->find(function($definition) use ($dataType) {
        return isset($definition['data_type']) && $definition['data_type'] == $dataType;
      }, $types);

      if (!empty($types)) {
        return array_pop($types);
      }

      array_pop($chain);
    }

    return NULL;
  }

  /**
   * Retrieve all mutations.
   *
   * @return object[]
   *   The list of mutation plugins.
   */
  public function getMutations() {
    return $this->find(function() {
      return TRUE;
    }, [GRAPHQL_MUTATION_PLUGIN]);
  }

  /**
   * Retrieve all fields that are not associated with a specific type.
   *
   * @return object[]
   *   The list root field plugins.
   */
  public function getRootFields() {
    // Retrieve the list of fields that are explicitly attached to a type.
    $attachedFields = array_reduce(array_filter(array_map(function($definition) {
      return array_key_exists('fields', $definition['definition']) ? $definition['definition']['fields'] : NULL;
    }, $this->getDefinitions())), 'array_merge', []);

    // Retrieve the list of fields that are not attached in any way or
    // explicitly attached to the artificial "Root" type.
    return $this->find(function($definition) use ($attachedFields) {
      return (!in_array($definition['name'], $attachedFields) && empty($definition['parents'])) || in_array('Root', $definition['parents']);
    }, [GRAPHQL_FIELD_PLUGIN]);
  }

  /**
   * Creates a type system plugin instance for a given plugin manager.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manager responsible for creation of the plugin instance.
   * @param $pluginType
   *   The plugin type.
   * @param $pluginId
   *   The plugin id.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The created plugin instance.
   */
  protected function getInstance(PluginManagerInterface $manager, $pluginType, $pluginId) {
    if (!isset($this->instances[$pluginType][$pluginId])) {
      // Initialize the static cache array if necessary.
      $this->instances[$pluginType] = isset($this->instances[$pluginType]) ? $this->instances[$pluginType] : [];

      // We do not allow plugin configuration for now.
      $instance = $manager->createInstance($pluginId);
      if (empty($instance)) {
        throw new \LogicException(sprintf('Could not instantiate plugin %s of type %s.', $pluginId, $pluginType));
      }

      if (!$instance instanceof TypeSystemPluginInterface) {
        throw new \LogicException(sprintf('Plugin %s of type %s does not implement \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface.', $pluginId, $pluginType));
      }

      // Prevent circular dependencies by building the type after constructing the plugin instance.
      $this->instances[$pluginType][$pluginId] = $instance;
      $this->instances[$pluginType][$pluginId]->buildConfig($this);
    }

    return $this->instances[$pluginType][$pluginId];
  }
}
