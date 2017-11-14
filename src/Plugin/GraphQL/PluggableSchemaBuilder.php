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
   * Static cache of plugin definitions.
   *
   * @var array
   */
  protected $definitions;

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
    $instances = [];
    foreach ($this->getDefinitions() as $index => $definition) {
      $name = $definition['definition']['name'];
      if (empty($name)) {
        throw new InvalidPluginDefinitionException('Invalid GraphQL plugin definition. No name defined.');
      }

      if (!array_key_exists($name, $instances) && in_array($definition['definition']['pluginType'], $types)) {
        if ((($invert && !$selector($definition['definition'])) || $selector($definition['definition']))) {
          $instances[$name] = $this->getInstance($definition['type'], $definition['id']);
        }
      }
    }

    return $instances;
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
   * Collects and aggregates all plugin definitions.
   *
   *
   * @return array
   *   The plugin definitions array.
   */
  protected function getDefinitions() {
    $this->definitions = [];

    /** @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager */
    foreach ($this->pluginManagers as $manager) {
      foreach ($manager->getDefinitions() as $pluginId => $definition) {
        $this->definitions[] = [
          'id' => $pluginId,
          'type' => $definition['pluginType'],
          'weight' => $definition['weight'],
          'definition' => $definition,
        ];
      }
    }

    uasort($this->definitions, '\Drupal\Component\Utility\SortArray::sortByWeightElement');
    $this->definitions = array_reverse($this->definitions);

    return $this->definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    // Don't write the plugin instances into the cache.
    return array_diff($this->sleepDependencies(), ['instances']);
  }
}
