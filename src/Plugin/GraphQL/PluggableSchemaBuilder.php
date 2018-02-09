<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;

class PluggableSchemaBuilder implements PluggableSchemaBuilderInterface {
  use DependencySerializationTrait {
    __sleep as sleepDependencies;
  }

  /**
   * The type system plugin manager aggregator service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator
   */
  protected $pluginManagers;

  /**
   * Static cache of type system plugin instances.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   */
  protected $instances = [];

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
   * {@inheritdoc}
   */
  public function getInstance($pluginType, $pluginId, array $pluginConfiguration = []) {
    $cid = $this->getCacheIdentifier($pluginType, $pluginId, $pluginConfiguration);
    if (!isset($this->instances[$cid])) {
      $managers = $this->pluginManagers->getPluginManagers($pluginType);
      if (empty($managers)) {
        throw new \LogicException(sprintf('Could not find %s plugin manager for plugin %s.', $pluginType, $pluginId));
      }

      // We do not allow plugin configuration for now.
      $instance = NULL;
      foreach ($managers as $manager) {
        if($manager->hasDefinition($pluginId)) {
          $instance = $manager->createInstance($pluginId, $pluginConfiguration);
        }
      }

      if (empty($instance)) {
        throw new \LogicException(sprintf('Failed to instantiate plugin %s of type %s.', $pluginId, $pluginType));
      }

      $this->instances[$cid] = $instance;
    }

    return $this->instances[$cid];
  }

  /**
   * {@inheritdoc}
   */
  public function find(callable $selector, array $types) {
    $items = [];

    /** @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager */
    foreach ($this->pluginManagers as $type => $managers) {
      if (!in_array($type, $types)) {
        continue;
      }

      foreach ($managers as $manager) {
        foreach ($manager->getDefinitions() as $id => $definition) {
          $name = $definition['name'];

          if (!array_key_exists($name, $items) || $items[$name]['weight'] < $definition['weight']) {
            if ($selector($definition)) {
              $items[$name] = [
                'weight' => $definition['weight'],
                'id' => $id,
                'type' => $type,
              ];
            }
          }
        }
      }
    }

    // Sort the plugins so that the ones with higher weight come first.
    usort($items, function (array $a, array $b) {
      if ($a['weight'] === $b['weight']) {
        return 0;
      }

      return ($a['weight'] < $b['weight']) ? 1 : -1;
    });

    return array_map(function (array $item) {
      return $this->getInstance($item['type'], $item['id']);
    }, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function findByDataType($type, array $types) {
    $parts = explode(':', $type);
    $chain = array_reverse(array_reduce($parts, function ($carry, $current) {
      return array_merge($carry, [implode(':', array_filter([end($carry), $current]))]);
    }, []), TRUE);

    $result = $this->find(function($definition) use ($chain) {
      if (!empty($definition['type'])) {
        foreach ($chain as $priority => $part) {
          if ($definition['type'] === $part) {
            return TRUE;
          }
        }
      }

      return FALSE;
    }, $types);

    return array_pop($result);
  }

  public function findByName($name, array $types) {
    $result = $this->find(function($definition) use ($name) {
      return $definition['name'] === $name;
    }, $types);

    return array_pop($result);
  }

  /**
   * {@inheritdoc}
   */
  public function findByDataTypeOrName($input, array $types) {
    if ($type = $this->findByDataType($input, $types)) {
      return $type;
    }

    if ($type = $this->findByName($input, $types)) {
      return $type;
    }

    return $this->getInstance('scalar', 'undefined');
  }

  /**
   * Creates a plugin instance cache identifier.
   *
   * @param string $pluginType
   *   The plugin type.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginConfiguration
   *   The plugin configuration.
   *
   * @return string
   */
  protected function getCacheIdentifier($pluginType, $pluginId, array $pluginConfiguration) {
    if (empty($pluginConfiguration)) {
      return "$pluginType:::$pluginId";
    }

    $configCid = md5(serialize($this->sortRecursive($pluginConfiguration)));
    return "$pluginType:::$pluginId:::$configCid";
  }

  /**
   * Recursively sorts an array.
   *
   * Useful for generating a cache identifiers.
   *
   * @param array $subject
   *   The array to sort.
   *
   * @return array
   *   The sorted array.
   */
  protected function sortRecursive(array $subject) {
    asort($subject);
    foreach ($subject as $key => $item) {
      if (is_array($item)) {
        $subject[$key] = $this->sortRecursive($item);
      }
    }

    return $subject;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    // Don't write the plugin instances into the cache.
    return array_diff($this->sleepDependencies(), ['instances']);
  }

}
