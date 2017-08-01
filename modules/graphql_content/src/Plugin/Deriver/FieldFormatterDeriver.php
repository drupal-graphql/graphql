<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
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
   * @return array
   *   Associative array of additional plugin definition values.
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    return [
      'types' => [
        graphql_camelcase([$entityType, $bundle]),
      ],
      'name' => graphql_propcase($storage->getName()),
      'virtual' => !$storage,
      'multi' => $storage ? $storage->getCardinality() != 1 : FALSE,
      'nullable' => TRUE,
      'field' => $storage->getName(),
    ];
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
      $storages = $this->entityFieldManager->getFieldStorageDefinitions($entityType);

      foreach ($display->getComponents() as $fieldName => $component) {
        if ($this->config->getExposedViewMode($entityType, $bundle) != $display->getMode()) {
          continue;
        }
        if (isset($component['type']) && $component['type'] === $basePluginDefinition['field_formatter']) {
          $storage = array_key_exists($fieldName, $storages) ? $storages[$fieldName] : NULL;
          /** @var \Drupal\Core\Field\FieldStorageDefinitionInterface $storage */
          $id = implode('-', [$entityType, $bundle, $storage->getName()]);
          $this->derivatives[$id] = [
            'id' => implode('-', [$entityType, $bundle, $storage->getName()]),
          ] + $this->getDefinition($entityType, $bundle, $component, $storage) + $basePluginDefinition;
        }
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
