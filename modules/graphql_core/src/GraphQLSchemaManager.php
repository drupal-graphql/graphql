<?php

namespace Drupal\graphql_core;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * GraphQL plugin manager.
 */
class GraphQLSchemaManager implements GraphQLSchemaManagerInterface {

  /**
   * Plugin managers, used to collect GraphQL schema plugins.
   *
   * @var PluginManagerInterface[]
   */
  protected $pluginManagers = [];

  /**
   * Static cache for sorted definitions.
   *
   * @var array
   */
  protected $definitions = NULL;

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
            'plugin_id' => $pluginId,
            'definition' => $definition,
            'weight' => $definition['weight'],
            'manager' => $manager,
          ];
        }
      }
      uasort($this->definitions, '\Drupal\Component\Utility\SortArray::sortByWeightElement');
      $this->definitions = array_reverse($this->definitions);
    }

    return $this->definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function find(callable $selector, array $types, $invert = FALSE) {
    $instances = [];
    foreach ($this->getDefinitions() as $index => $def) {
      $name = $def['definition']['name'];
      if (!$name) {
        throw new \Exception("Invalid GraphQL plugin definition. No name defined.");
      }
      if (!array_key_exists($name, $instances) && in_array($def['definition']['pluginType'], $types)) {
        if ((($invert && !$selector($def['definition'])) || $selector($def['definition']))) {
          /** @var PluginManagerInterface $manager */
          $manager = $def['manager'];
          $instances[$name] = $manager->createInstance($def['plugin_id']);
        }
      }
    }
    return $instances;
  }

  /**
   * {@inheritdoc}
   */
  public function findByName($name, array $types) {
    $result = $this->find(function ($definition) use ($name) {
      return $definition['name'] == $name;
    }, $types);

    if (!$result) {
      throw new \Exception('GraphQL plugin with name ' . $name . ' could not be found.');
    }

    return array_pop($result);
  }

  /**
   * Add a plugin manager.
   *
   * @param PluginManagerInterface $pluginManager
   *   The plugin manager to attach.
   */
  public function addPluginManager(PluginManagerInterface $pluginManager) {
    $this->pluginManagers[] = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getMutations() {
    return $this->find(function () {
      return TRUE;
    }, [GRAPHQL_CORE_MUTATION_PLUGIN]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRootFields() {
    // Retrieve the list of fields that are explicitly attached to a type.
    $attachedFields = array_reduce(array_filter(array_map(function ($def) {
      return array_key_exists('fields', $def['definition']) ? $def['definition']['fields'] : NULL;
    }, $this->getDefinitions())), 'array_merge', []);

    // Retrieve the list of fields that are not attached in any way or
    // explicitly attached to the artificial "Root" type.
    return $this->find(function ($def) use ($attachedFields) {
      return (!in_array($def['name'], $attachedFields) && empty($def['types'])) || in_array('Root', $def['types']);
    }, [GRAPHQL_CORE_FIELD_PLUGIN]);
  }

}
