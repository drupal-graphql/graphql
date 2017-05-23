<?php

namespace Drupal\graphql_content_mutation\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityInputFieldDeriver extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      foreach ($this->entityFieldManager->getFieldStorageDefinitions($entityTypeId) as $fieldName => $field) {
        $properties = [];

        foreach ($field->getPropertyDefinitions() as $propertyName => $propertyDefinition) {
          if ($propertyDefinition->isReadOnly() || $propertyDefinition->isComputed()) {
            continue;
          }

          $properties[graphql_core_propcase($propertyName)] = [
            'type' => 'String',
            'nullable' => !$propertyDefinition->isRequired(),
            'multi' => $propertyDefinition->isList(),
            'property_name' => $propertyName,
          ];
        }

        $this->derivatives["$entityTypeId:$fieldName"] = [
          'name' => graphql_core_camelcase([$entityTypeId, $fieldName]) . 'FieldInput',
          'fields' => $properties,
          'entity_type' => $entityTypeId,
          'field_name' => $fieldName,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
