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
    }

    return $this->instances[$pluginId];
  }

}
