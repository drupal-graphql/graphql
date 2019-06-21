<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\Discovery\GqlExtendedDiscovery;
use Drupal\graphql\Discovery\GqlDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows modules to define MODULE_NAME.gql schema files.
 *
 * This class discovers MODULE_NAME.gql and MODULE_NAME.extend.gql in all
 * modules and concatenates them into one graphql schema.
 */
abstract class SdlModuleSchemaPluginBase extends SdlExtendedSchemaPluginBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, CacheBackendInterface $astCache, $config, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $astCache, $config);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->getParameter('graphql.config'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    // Build our schema from all modules that provide a MODULE_NAME.gql file.
    $discovery = new GqlDiscovery($this->moduleHandler->getModuleDirectories());
    $schema_parts = $discovery->findAll();
    return implode("\n", $schema_parts);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtendedSchemaDefinition() {
    // Build our extended schema from all modules that provide a
    // MODULE_NAME.extend.gql file.
    $discovery = new GqlExtendedDiscovery($this->moduleHandler->getModuleDirectories());
    $schema_parts = $discovery->findAll();
    return implode("\n", $schema_parts);
  }

}
