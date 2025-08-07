<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manager that collects and exposes GraphQL schema extension plugins.
 *
 * @package Drupal\graphql\Plugin
 *
 * @codeCoverageIgnore
 */
class SchemaExtensionPluginManager extends DefaultPluginManager {

  /**
   * Static cache of plugin instances per schema plugin.
   *
   * @var array<array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface>>
   */
  protected array $extensions;

  /**
   * SchemaExtensionPluginManager constructor.
   *
   * @param bool|string $pluginSubdirectory
   *   The plugin's subdirectory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param string|null $pluginInterface
   *   The interface each plugin should implement.
   * @param string $pluginAttributeName
   *   The name of the attribute that contains the plugin definition.
   * @param string $pluginAnnotationName
   *   The name of the annotation that contains the plugin definition.
   * @param array $config
   *   The configuration service parameter.
   */
  public function __construct(
    bool|string $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $cacheBackend,
    ?string $pluginInterface,
    string $pluginAttributeName,
    string $pluginAnnotationName,
    array $config,
  ) {
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAttributeName,
      $pluginAnnotationName
    );

    $this->alterInfo('graphql_schema_extension');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($cacheBackend, 'graphql_schema_extension', ['graphql_schema_extension']);
  }

  /**
   * Retrieves the schema extension plugin instances for a given schema plugin.
   *
   * @param string $id
   *   The id of the schema plugin to retrieve the extensions for.
   *
   * @return array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface>
   *   An array of schema extension plugin instances sorted by priority.
   */
  public function getExtensions(string $id): array {
    if (!isset($this->extensions[$id])) {
      $this->extensions[$id] = array_map(function ($definition) {
        return $this->createInstance($definition['id']);
      }, array_filter($this->getDefinitions(), function ($definition) use ($id) {
        return $definition['schema'] === $id;
      }));
    }

    return self::sortByPriority($this->extensions[$id]);
  }

  /**
   * Sorts the given schema extension plugins by priority.
   *
   * @param array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface> $extensions
   *   The schema extension plugins to sort.
   *
   * @return array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface>
   *   The sorted schema extension plugins.
   */
  public static function sortByPriority(array $extensions): array {
    uasort($extensions, function (SchemaExtensionPluginInterface $a, SchemaExtensionPluginInterface $b) {
      $priority_a = $a->getPluginDefinition()['priority'] ?? 0;
      $priority_b = $b->getPluginDefinition()['priority'] ?? 0;
      return $priority_b <=> $priority_a;
    });

    return $extensions;
  }

}
