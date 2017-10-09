<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Psr\Log\LoggerInterface;
use Traversable;

/**
 * Base class for type system plugin managers or all sorts.
 */
class TypeSystemPluginManager extends DefaultPluginManager {

  /**
   * Static cache for plugin instances.
   *
   * @var object[]
   */
  protected $instances = [];

  /**
   * An instance of the GraphQL schema manager to pull dependencies.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManager
   *   Reference to the GraphQL schema manager.
   */
  protected $schemaManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $pluginSubdirectory,
    Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    $pluginInterface,
    $pluginAnnotationName,
    $alterInfo,
    LoggerInterface $logger
  ) {
    $this->alterInfo($alterInfo);
    $this->logger = $logger;

    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

  /**
   * Set the schema manager service.
   *
   * Setter injection is required in this case due to the circular dependency
   * between the plugin manager and the schema manager.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface $schemaManager
   *   The schema manager service.
   *
   * @return $this
   */
  public function setSchemaManager(PluggableSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;

    return $this;
  }

  /**
   * Retrieves the schema manager service.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface
   *   The schema manager service.
   */
  public function getSchemaManager() {
    return $this->schemaManager;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($pluginId, array $configuration = []) {
    if (!array_key_exists($pluginId, $this->instances)) {
      // We deliberately ignore that $configuration could be different, because
      // GraphQL plugins don't contain user defined configuration.
      $this->instances[$pluginId] = parent::createInstance($pluginId);
      if (!$this->instances[$pluginId] instanceof TypeSystemPluginInterface) {
        throw new \LogicException(sprintf('Plugin %s does not implement \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface.', $pluginId));
      }

      try {
        $this->instances[$pluginId]->buildConfig($this->schemaManager);
      }
      catch (\Exception $exception) {
        $this->logger->warning(sprintf('Plugin %s could not be added to the GraphQL schema: %s', $pluginId, $exception->getMessage()));
        $this->instances[$pluginId]->buildConfig($this->schemaManager);
        $this->instances[$pluginId] = NULL;
      }
    }
    return $this->instances[$pluginId];
  }

}
