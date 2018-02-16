<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class TypeSystemPluginManager extends DefaultPluginManager implements TypeSystemPluginManagerInterface {
  use DependencySerializationTrait;

  /**
   * The plugin manager aggregator service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator
   */
  protected $pluginManagerAggregator;

  /**
   * TypeSystemPluginManager constructor.
   *
   * @param bool|string $pluginSubdirectory
   *   The plugin's subdirectory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator $pluginManagerAggregator
   *   The plugin manager aggregator service.
   * @param string|null $pluginInterface
   *   The interface each plugin should implement.
   * @param string $pluginAnnotationName
   *   The name of the annotation that contains the plugin definition.
   * @param string $pluginType
   *   The plugin type.
   */
  public function __construct(
    $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    TypeSystemPluginManagerAggregator $pluginManagerAggregator,
    $pluginInterface,
    $pluginAnnotationName,
    $pluginType
  ) {
    // Allow altering plugin definitions through a hook.
    $this->alterInfo($pluginType);

    // Cache plugin definitions.
    $this->useCaches(TRUE);

    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );

    // The plugin manager aggregator is used for cache handling.
    $this->pluginManagerAggregator = $pluginManagerAggregator;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return substr(strlen('grapqhl_'), $this->alterHook);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey() {
    if (isset($this->_serviceId)) {
      return $this->_serviceId;
    }

    throw new \LogicException('Missing cache identifier.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($id, $exception = TRUE) {
    return $this->doGetDefinition($this->getDefinitions(), $id, $exception);
  }

  /**
   * {@inheritdoc}
   */
  protected function getCachedDefinitions() {
    return $this->pluginManagerAggregator->cacheGet($this);
  }

  /**
   * {@inheritdoc}
   */
  protected function setCachedDefinitions($definitions) {
    return $this->pluginManagerAggregator->cacheSet($this, $definitions);
  }


}
