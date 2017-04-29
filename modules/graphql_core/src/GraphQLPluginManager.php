<?php

namespace Drupal\graphql_core;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManager;
use Traversable;

/**
 * Base class for GraphQL Plugin managers.
 */
class GraphQLPluginManager extends DefaultPluginManager {

  /**
   * Static cache for plugin instances.
   *
   * @var object[]
   */
  protected $instances = [];

  /**
   * An instance of the GraphQL schema manager to pull dependencies.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManager
   *   Reference to the GraphQL schema manager.
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $pluginSubdirectory,
    Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
    $pluginInterface,
    $pluginAnnotationName,
    GraphQLSchemaManager $schemaManager,
    $alterInfo
  ) {
    $this->schemaManager = $schemaManager;
    $this->alterInfo($alterInfo);
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $module_handler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    if (!array_key_exists($plugin_id, $this->instances)) {
      // We deliberately ignore that $configuration could be different, because
      // GraphQL plugins don't contain user defined configuration.
      $this->instances[$plugin_id] = parent::createInstance($plugin_id);
      if ($this->instances[$plugin_id] instanceof GraphQLPluginInterface) {
        $this->instances[$plugin_id]->buildConfig($this->schemaManager);
      }
    }
    return $this->instances[$plugin_id];
  }

}
