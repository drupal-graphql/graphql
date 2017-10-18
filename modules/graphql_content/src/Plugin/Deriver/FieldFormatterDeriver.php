<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_content\ContentEntitySchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate GraphQLField plugins for certain field formatters.
 */
class FieldFormatterDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The content entity schema configuration service.
   *
   * @var \Drupal\graphql_content\ContentEntitySchemaConfig
   */
  protected $config;

  /**
   * The base plugin id.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('graphql_content.schema_config'),
      $basePluginId);
  }

  /**
   * AbstractFieldFormatterDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   An entity type manager instance.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   An entity field manager instance.
   * @param \Drupal\graphql_content\ContentEntitySchemaConfig $config
   *   A schema configuration service.
   * @param string $basePluginId
   *   The base plugin id.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    ContentEntitySchemaConfig $config,
    $basePluginId
  ) {
    $this->basePluginId = $basePluginId;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->config = $config;
  }

  /**
   * Provide plugin definition values from field storage and display options.
   *
   * @param string $entityType
   *   The host entity type.
   * @param string $bundle
   *   The host entity bundle.
   * @param array $displayOptions
   *   Array of display options.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface|null $storage
   *   Field storage definition object.
   *
   * @return array|null
   *   Associative array of additional plugin definition values.
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    if (isset($storage)) {
      return [
        'parents' => [StringHelper::camelCase([$entityType, $bundle])],
        'name' => graphql_propcase($storage->getName()),
        'virtual' => !$storage,
        'multi' => $storage ? $storage->getCardinality() != 1 : FALSE,
        'nullable' => TRUE,
        'field' => $storage->getName(),
      ];
    }

    return NULL;
  }

  /**
   * Provide an array of plugin definition values from field storage and display
   * options.
   *
   * @param string $entityType
   *   The host entity type.
   * @param string $bundle
   *   The host entity bundle.
   * @param array $displayOptions
   *   Array of display options.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface|null $storage
   *   Field storage definition object.
   *
   * @return array
   *   An array of plugin definition arrays.
   */
  protected function getDefinitions($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    if ($definition = $this->getDefinition($entityType, $bundle, $displayOptions, $storage)) {
      $id = implode('-', [$entityType, $bundle, $storage->getName()]);
      return [$id => $definition + [
        'id' => $id,
      ]];
    }

    return [];
  }
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface[] $displays */
    $displays = $this->entityTypeManager->getStorage('entity_view_display')->loadMultiple();
    foreach ($displays as $display) {
      $entityType = $display->getTargetEntityTypeId();
      $bundle = $display->getTargetBundle();

      if ($this->config->getExposedViewMode($entityType, $bundle) !== $display->getMode()) {
        continue;
      }

      $storages = $this->entityFieldManager->getFieldStorageDefinitions($entityType);
      foreach ($display->getComponents() as $fieldName => $component) {
        if (!isset($component['type']) || $component['type'] !== $basePluginDefinition['field_formatter']) {
          continue;
        }

        if (!array_key_exists($fieldName, $storages)) {
          continue;
        }

        /** @var \Drupal\Core\Field\FieldStorageDefinitionInterface $storage */
        $storage = $storages[$fieldName];
        if ($definitions = $this->getDefinitions($entityType, $bundle, $component, $storage)) {
          foreach ($definitions as $id => $definition) {
            $this->derivatives[$id] = $definition + $basePluginDefinition;
          }
        }
      }
    }

    return $this->derivatives;
  }

}
