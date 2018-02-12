<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Traversable;

/**
 * Base class for type system plugin managers or all sorts.
 */
class TypeSystemPluginManager extends DefaultPluginManager implements TypeSystemPluginManagerInterface {
  use DependencySerializationTrait;

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
   * @param string|null $pluginInterface
   *   The interface each plugin should implement.
   * @param string $pluginAnnotationName
   *   The name of the annotation that contains the plugin definition.
   * @param string $pluginType
   *   The plugin type.
   */
  public function __construct(
    $pluginSubdirectory,
    Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    $pluginInterface,
    $pluginAnnotationName,
    $pluginType
  ) {
    // Allow altering plugin definitions through a hook.
    $this->alterInfo($pluginType);

    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

  /**
   * Returns the type of plugin handled by this plugin manager.
   *
   * @return string
   *   The plugin type handled by this manager.
   */
  public function getPluginType() {
    return substr(strlen('grapqhl_'), $this->alterHook);
  }

}
