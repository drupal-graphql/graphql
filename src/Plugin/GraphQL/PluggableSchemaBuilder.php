<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * TODO: This thing was just quickly put together to make the PoC work. Fix it!
 */
class PluggableSchemaBuilder implements PluggableSchemaBuilderInterface {
  use DependencySerializationTrait;

  /**
   * The type system plugin manager aggregator service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator
   */
  protected $pluginManagers;

  /**
   * PluggableSchemaBuilder constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator $pluginManagers
   *   Type system plugin manager aggregator service.
   */
  public function __construct(TypeSystemPluginManagerAggregator $pluginManagers) {
    $this->pluginManagers = $pluginManagers;
  }

  /**
   * @param $type
   * @param $id
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface
   */
  public function getPluginManager($type, $id) {
    $managers = $this->pluginManagers->getPluginManagers($type);
    foreach ($managers as $manager) {
      if ($manager->hasDefinition($id)) {
        return $manager;
      }
    }

    throw new \LogicException('Could not find plugin manager.');
  }

  /**
   * @param $type
   * @param $id
   *
   * @return mixed
   */
  public function getPluginDefinition($type, $id) {
    return $this->getPluginManager($type, $id)->getDefinition($id);
  }

  /**
   * @param $type
   * @param $id
   *
   * @return object
   */
  public function getPluginInstance($type, $id) {
    if (!isset($this->plugins[$type][$id])) {
      return $this->plugins[$type][$id] = $this->getPluginManager($type, $id)->createInstance($id);
    }

    return $this->plugins[$type][$id];
  }

  /**
   * @param $type
   * @param $id
   *
   * @return mixed
   */
  public function getDefinition($type, $id) {
    return $this->getPluginInstance($type, $id)->getDefinition();
  }

  /**
   * @param $type
   * @param $id
   *
   * @return mixed
   */
  public function getType($type, $id) {
    if (!isset($this->instances[$type][$id])) {
      $class = DefaultFactory::getPluginClass($id, $this->getPluginDefinition($type, $id));
      $definition = $this->getDefinition($type, $id);
      return $this->instances[$type][$id] = call_user_func([$class, 'createInstance'], $this, $definition, $id);
    }

    return $this->instances[$type][$id];
  }

  /**
   * @return array
   */
  public function getTypeMap() {
    if (isset($this->typeMap)) {
      return $this->typeMap;
    }

    foreach ($this->pluginManagers as $type => $managers) {
      if ($type === GRAPHQL_FIELD_PLUGIN || $type === GRAPHQL_MUTATION_PLUGIN) {
        continue;
      }

      /** @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager */
      foreach ($managers as $manager) {
        foreach ($manager->getDefinitions() as $id => $definition) {
          $placeholder = &$this->typeMap[$definition['name']];

          if (!empty($placeholder)) {
            $existing = $this->getPluginDefinition($placeholder[0], $placeholder[1]);
            // Do not override if the existing plugin has a higher weight.
            if ($existing['weight'] > $definition['weight']) {
              continue;
            }
          }

          $placeholder = [$type, $id];
          if (isset($definition['type'])) {
            $this->typeMap[$definition['type']] = &$placeholder;
          }
        }
      }
    }

    return $this->typeMap = array_filter($this->typeMap);
  }

  /**
   * @return array
   */
  public function getFieldMap() {
    if (isset($this->fieldMap)) {
      return $this->fieldMap;
    }

    $this->fieldMap = [];
    foreach ($this->pluginManagers->getPluginManagers(GRAPHQL_FIELD_PLUGIN) as $manager) {
      foreach ($manager->getDefinitions() as $id => $definition) {
        $parents = $definition['parents'] ?: ['Root'];

        foreach ($parents as $parent) {
          $placeholder = &$this->fieldMap[$parent][$definition['name']];

          if (!empty($placeholder)) {
            // Do not override if the existing plugin has a higher weight.
            $existing = $this->getPluginDefinition(GRAPHQL_FIELD_PLUGIN, $placeholder);
            if ($existing['weight'] > $definition['weight']) {
              continue;
            }
          }

          $placeholder = $id;
        }
      }
    }

    return $this->fieldMap;
  }

  /**
   * @return array
   */
  public function getMutationMap() {
    if (isset($this->mutationMap)) {
      return $this->mutationMap;
    }

    $this->mutationMap = [];
    foreach ($this->pluginManagers->getPluginManagers(GRAPHQL_MUTATION_PLUGIN) as $manager) {
      foreach ($manager->getDefinitions() as $id => $definition) {
        $placeholder = &$this->mutationMap[$definition['name']];

        if (!empty($placeholder)) {
          // Do not override if the existing plugin has a higher weight.
          $existing = $this->getPluginDefinition(GRAPHQL_MUTATION_PLUGIN, $placeholder);
          if ($existing['weight'] > $definition['weight']) {
            continue;
          }
        }

        $placeholder = $id;
      }
    }

    return $this->mutationMap;
  }

  /**
   * @param $name
   *
   * @return mixed
   */
  public function getTypeByName($name) {
    $map = $this->getTypeMap();
    if (isset($map[$name])) {
      return $this->getType($map[$name][0], $map[$name][1]);
    }

    while (($pos = strpos($name, ':')) !== FALSE && $name = substr($name, 0, $pos)) {
      if (isset($map[$name])) {
        return $this->getType($map[$name][0], $map[$name][1]);
      }
    }

    throw new \LogicException('Could not find type in type map.');
  }

  /**
   * @param $name
   *
   * @return array
   */
  public function getFieldsByType($name) {
    $map = $this->getFieldMap();
    if (empty($map[$name])) {
      return [];
    }

    return $this->resolveFields($map[$name]);
  }

  /**
   * @param $fields
   *
   * @return array
   */
  public function resolveFields($fields) {
    return array_map(function ($id) use ($fields) {
      $field = $this->getType(GRAPHQL_FIELD_PLUGIN, $id);
      list($type, $decorators) = $field['type'];

      $type = array_reduce($decorators, function ($a, $decorator) use ($type) {
        return $decorator($a);
      }, $this->getTypeByName($type));

      return [
        'type' => $type,
      ] + $field;
    }, $fields);
  }

  /**
   * @param $args
   *
   * @return array
   */
  public function resolveArgs($args) {
    return array_map(function ($arg) {
      list($type, $decorators) = $arg['type'];

      $type = array_reduce($decorators, function ($a, $decorator) use ($type) {
        return $decorator($a);
      }, $this->getTypeByName($type));

      return [
        'type' => $type,
      ] + $arg;
    }, $args);
  }

}
