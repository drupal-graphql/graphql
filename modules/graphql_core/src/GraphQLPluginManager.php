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
    ModuleHandlerInterface $moduleHandler,
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
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($pluginId, array $configuration = []) {
    if (!array_key_exists($pluginId, $this->instances)) {
      // We deliberately ignore that $configuration could be different, because
      // GraphQL plugins don't contain user defined configuration.
      $this->instances[$pluginId] = parent::createInstance($pluginId);
      if ($this->instances[$pluginId] instanceof GraphQLPluginInterface) {
        $this->instances[$pluginId]->buildConfig($this->schemaManager);
      }
    }
    return $this->instances[$pluginId];
  }

}
