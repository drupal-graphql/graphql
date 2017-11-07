<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Attach new properties to field types.
 */
class EntityFieldPropertyDeriver extends DeriverBase implements ContainerDeriverInterface {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * RawValueFieldItemDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
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
    $parents = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
      $interfaces = class_implements($entityType->getClass());
      if (!array_key_exists(FieldableEntityInterface::class, $interfaces)) {
        continue;
      }

      $fieldDefinitions = $this->entityFieldManager->getFieldStorageDefinitions($entityTypeId);
      foreach ($fieldDefinitions as $fieldDefinition) {
        $fieldName = $fieldDefinition->getName();
        $fieldType = $fieldDefinition->getType();

        if (isset($basePluginDefinition['field_types']) && in_array($fieldType, $basePluginDefinition['field_types'])) {
          $parents[] = EntityFieldType::getId($entityTypeId, $fieldName);
        }
      }
    }

    if (!empty($parents)) {
      $this->derivatives[$basePluginDefinition['id']] = [
        'parents' => array_merge($parents, $basePluginDefinition['parents']),
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }
}