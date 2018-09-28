<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityReferenceReverseDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

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
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('typed_data_manager')
    );
  }

  /**
   * RawValueFieldItemDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    TypedDataManagerInterface $typedDataManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
      $interfaces = class_implements($entityType->getClass());
      if (!array_key_exists(FieldableEntityInterface::class, $interfaces)) {
        continue;
      }

      foreach ($this->entityFieldManager->getFieldStorageDefinitions($entityTypeId) as $fieldDefinition) {
        if ($fieldDefinition->getType() !== 'entity_reference' || !$targetTypeId = $fieldDefinition->getSetting('target_type')) {
          continue;
        }

        $tags = array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']);
        $contexts = $fieldDefinition->getCacheContexts();
        $maxAge = $fieldDefinition->getCacheMaxAge();

        $fieldName = $fieldDefinition->getName();
        $derivative = [
          'parents' => [StringHelper::camelCase($targetTypeId)],
          'name' => StringHelper::propCase('reverse', $fieldName, $entityTypeId),
          'description' => $this->t('Reverse reference: @description', [
            '@description' => $fieldDefinition->getDescription(),
          ]),
          'field' => $fieldName,
          'entity_type' => $entityTypeId,
          'schema_cache_tags' => $tags,
          'schema_cache_contexts' => $contexts,
          'schema_cache_max_age' => $maxAge,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $this->derivatives["$entityTypeId-$fieldName"] = $derivative;
      }
    }

    return $this->derivatives;
  }
}
