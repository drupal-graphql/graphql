<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Traversable;

class SchemaPluginManager extends DefaultPluginManager {

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
    $this->alterInfo($pluginType);

    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

}
