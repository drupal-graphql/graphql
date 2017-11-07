<?php

namespace Drupal\graphql_core\Plugin\Deriver\InputTypes;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityQueryFilterInputDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

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
          'name' => StringHelper::camelCase($id, 'query', 'filter', 'input'),
          'description' => $this->t("Entity query filter input type for loading '@type' entities.", [
            '@type' => $type->getLabel(),
          ]),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $definition = $this->typedDataManager->createDataDefinition("entity:$id");
        $properties = $definition->getPropertyDefinitions();

        $queryableProperties = array_filter($properties, function($property) {
          return $property instanceof BaseFieldDefinition && $property->isQueryable();
        });

        // Don't even create the type if there are no queryable properties.
        if (empty($queryableProperties)) {
          continue;
        }

        // Add all queryable properties as fields.
        foreach ($queryableProperties as $key => $property) {
          $fieldName = StringHelper::propCase($key);

          // Some field types don't have a main property.
          if (!$mainProperty = $property->getMainPropertyName()) {
            continue;
          }

          // Some field types are broken and define a non-existant main property.
          if (!$mainPropertyDefinition = $property->getPropertyDefinition($mainProperty)) {
            continue;
          }

          $derivative['fields'][$fieldName] = [
            'multi' => FALSE,
            'nullable' => TRUE,
            'field_name' => $key,
            'data_type' => $mainPropertyDefinition->getDataType(),
          ];
        }

        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
