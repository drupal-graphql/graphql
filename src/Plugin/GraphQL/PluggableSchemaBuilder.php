<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
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
   * PluggableSchemaBuilderInterface constructor.
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
    asort($pluginConfiguration);
    $configCid = md5(json_encode($pluginConfiguration));

    if (!isset($this->instances[$pluginType][$pluginId][$configCid])) {
      $manager = $this->pluginManagers->getPluginManager($pluginType);
      if (empty($manager)) {
        throw new \LogicException(sprintf('Could not find %s plugin manager for plugin %s.', $pluginType, $pluginId));
      }

      // We do not allow plugin configuration for now.
      $instance = $manager->createInstance($pluginId, $pluginConfiguration);
      if (empty($instance)) {
        throw new \LogicException(sprintf('Could not instantiate plugin %s of type %s.', $pluginId, $pluginType));
      }

      if (!$instance instanceof TypeSystemPluginInterface) {
        throw new \LogicException(sprintf('Plugin %s of type %s does not implement \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface.', $pluginId, $pluginType));
      }

      // Initialize the static cache array if necessary.
      $this->instances[$pluginType] = isset($this->instances[$pluginType]) ? $this->instances[$pluginType] : [];
      $this->instances[$pluginType][$pluginId] = isset($this->instances[$pluginType][$pluginId]) ? $this->instances[$pluginType][$pluginId] : [];
      $this->instances[$pluginType][$pluginId][$configCid] = $instance;
    }

    return $this->instances[$pluginType][$pluginId][$configCid];
  }

  /**
   * {@inheritdoc}
   */
  public function find(callable $selector, array $types, $invert = FALSE) {
    $items = [];

    /** @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager */
    foreach ($this->pluginManagers as $type => $manager) {
      if (!in_array($type, $types)) {
        continue;
      }

      foreach ($manager->getDefinitions() as $id => $definition) {
        $name = $definition['name'];
        if (empty($name)) {
          throw new InvalidPluginDefinitionException('Invalid GraphQL plugin definition. No name defined.');
        }

        if (!array_key_exists($name, $items) || $items[$name]['weight'] < $definition['weight']) {
          if ((($invert && !$selector($definition)) || $selector($definition))) {
            $items[$name] = [
              'weight' => $definition['weight'],
              'id' => $id,
              'type' => $type,
            ];
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function __sleep() {
    // Don't write the plugin instances into the cache.
    return array_diff($this->sleepDependencies(), ['instances']);
  }
}
