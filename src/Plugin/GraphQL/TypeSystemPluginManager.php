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
   * {@inheritdoc}
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
    return $this->alterHook;
  }

}
