<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManager;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator;

/**
 * Wraps an existing type system plugin manager and adds mock plugins.
 */
class MockTypeSystemPluginManager extends TypeSystemPluginManager {

  /**
   * The mock plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $mockPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    TypeSystemPluginManagerAggregator $pluginManagerAggregator,
    string $pluginInterface,
    string $pluginAnnotationName,
    string $pluginType,
    PluginManagerInterface $mockPluginManager
  ) {
    $this->mockPluginManager = $mockPluginManager;
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginManagerAggregator,
      $pluginInterface,
      $pluginAnnotationName,
      $pluginType
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return parent::getDefinitions() + $this->mockPluginManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function hasDefinition($plugin_id) {
    return parent::hasDefinition($plugin_id) || $this->mockPluginManager->hasDefinition($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    if ($this->mockPluginManager->hasDefinition($plugin_id)) {
      return $this->mockPluginManager->createInstance($plugin_id, $configuration);
    }
    return parent::createInstance($plugin_id, $configuration);
  }

}