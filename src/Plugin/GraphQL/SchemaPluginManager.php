<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Traversable;

class SchemaPluginManager extends DefaultPluginManager {

  /**
   * Static cache for plugin instances.
   *
   * @var object[]
   */
  protected $instances = [];

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $pluginSubdirectory,
    Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    $pluginInterface,
    $pluginAnnotationName,
    $pluginType,
    ContainerInterface $container
  ) {
    $this->container = $container;
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
   * {@inheritdoc}
   */
  public function createInstance($pluginId, array $configuration = []) {
    if (!array_key_exists($pluginId, $this->instances)) {
      $this->instances[$pluginId] = parent::createInstance($pluginId);
      if (!$this->instances[$pluginId] instanceof SchemaPluginInterface) {
        throw new \LogicException(sprintf('Plugin %s does not implement \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface.', $pluginId));
      }

      $schemaBuilder = $this->getSchemaBuilder($pluginId);
      $this->instances[$pluginId]->buildConfig($schemaBuilder);
    }

    return $this->instances[$pluginId];
  }

  /**
   * Retrieves a schema builder instance.
   *
   * @param string $pluginId
   *   The plugin id.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface|NULL The schema builder.
   *   The schema builder.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSchemaBuilder($pluginId) {
    $pluginDefinition = $this->getDefinition($pluginId);
    if (empty($pluginDefinition['builder'])) {
      return NULL;
    }

    $class = $pluginDefinition['builder'];
    if (!is_subclass_of($class, '\Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface')) {
      throw new InvalidPluginDefinitionException(sprintf('The schema builder for plugin %s does not implement \Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface.', $schema->getPluginId()));
    }

    return $class::createInstance($this->container, $pluginId, $pluginDefinition);
  }

}
