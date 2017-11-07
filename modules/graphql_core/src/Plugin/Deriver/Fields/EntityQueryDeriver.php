<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL entityQuery fields based on available Drupal entity types.
 */
class EntityQueryDeriver extends DeriverBase implements ContainerDeriverInterface {
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
          'name' => StringHelper::propCase($id, 'query'),
          'description' => $this->t("Loads '@type' entities.", ['@type' => $type->getLabel()]),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $definition = $this->typedDataManager->createDataDefinition("entity:$id");
        $properties = $definition->getPropertyDefinitions();

        $queryableProperties = array_filter($properties, function ($property) {
          return $property instanceof BaseFieldDefinition && $property->isQueryable();
        });

        if (!empty($queryableProperties)) {
          $derivative['arguments']['filter'] = [
            'multi' => FALSE,
            'nullable' => TRUE,
            'type' => StringHelper::camelCase($id, 'query', 'filter', 'input'),
          ];
        }

        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
