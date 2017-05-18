<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for graphql view derivers.
 */
abstract class ViewDeriverBase extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The interface plugin manager to search for return type candidates.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $interfacePluginManager;

  /**
   * An key value pair of data tables and the entities they belong to.
   *
   * @var string[]
   */
  protected $dataTables;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('graphql_core.interface_manager')
    );
  }

  /**
   * Creates a ViewDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   An entity type manager instance.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $interfacePluginManager
   *   The plugin manager for graphql interfaces.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    PluginManagerInterface $interfacePluginManager
  ) {
    $this->interfacePluginManager = $interfacePluginManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Check if a pager is configured.
   *
   * @param array $display
   *   The display configuration.
   *
   * @return bool
   *   Flag indicating if the view is configured with a pager.
   */
  protected function isPaged(array $display) {
    return in_array(NestedArray::getValue($display, [
      'display_options', 'pager', 'type',
    ]) ?: 'none', ['full', 'mini']);
  }

  /**
   * Get the configured default limit.
   *
   * @param array $display
   *   The display configuration.
   *
   * @return int
   *   The default limit.
   */
  protected function getPagerLimit(array $display) {
    return NestedArray::getValue($display, [
      'display_options', 'pager', 'options', 'items_per_page',
    ]) ?: 0;
  }

  /**
   * Get the configured default offset.
   *
   * @param array $display
   *   The display configuration.
   *
   * @return int
   *   The default offset.
   */
  protected function getPagerOffset(array $display) {
    return NestedArray::getValue($display, [
      'display_options', 'pager', 'options', 'offset',
    ]) ?: 0;
  }

  /**
   * Retrieves the entity type id of an entity by its base or data table.
   *
   * @param string $table
   *   The base or data table of an entity.
   *
   * @return string
   *   The id of the entity type that the given base table belongs to.
   */
  protected function getEntityTypeByTable($table) {
    if (!isset($this->dataTables)) {
      $this->dataTables = [];

      foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
        if ($dataTable = $entityType->getDataTable()) {
          $this->dataTables[$dataTable] = $entityType->id();
        }
        if ($baseTable = $entityType->getBaseTable()) {
          $this->dataTables[$baseTable] = $entityType->id();
        }
      }
    }

    return !empty($this->dataTables[$table]) ? $this->dataTables[$table] : NULL;
  }

  /**
   * Check if a certain interface exists.
   *
   * @param string $interface
   *   The GraphQL interface name.
   *
   * @return bool
   *   Boolean flag indicating if the interface exists.
   */
  protected function interfaceExists($interface) {
    return (bool) array_filter($this->interfacePluginManager->getDefinitions(), function ($definition) use ($interface) {
      return $definition['name'] === $interface;
    });
  }

}
