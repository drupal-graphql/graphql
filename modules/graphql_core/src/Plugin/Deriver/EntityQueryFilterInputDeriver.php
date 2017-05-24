<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL entityQuery fields based on available Drupal entity types.
 */
class EntityQueryFilterInputDeriver extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('typed_data_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    TypedDataManager $typedDataManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $derivative = [
          'name' => graphql_core_camelcase([$id, 'query', 'filter', 'input']),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $definition = $this->typedDataManager->createDataDefinition("entity:$id");
        $properties = $definition->getPropertyDefinitions();

        $queryable_properties = array_filter($properties, function ($property) {
          return $property instanceof BaseFieldDefinition && $property->isQueryable();
        });

        // Don't even create the type if there are no queryable properties.
        if (!$queryable_properties) {
          continue;
        }

        // Add all queryable properties as fields.
        foreach ($queryable_properties as $key => $property) {
          $fieldName = graphql_core_propcase($key);

          // Some field types don't have a main property.
          if (!$mainProperty = $property->getMainPropertyName()) {
            continue;
          }

          $mainPropertyDataType = $property->getPropertyDefinition($mainProperty)->getDataType();

          $derivative['fields'][$fieldName] = [
            'multi' => FALSE,
            'nullable' => TRUE,
            'data_type' => $mainPropertyDataType,
          ];
        }

        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
