<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql_content\ContentEntitySchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL entityQuery fields based on available Drupal entity types.
 */
class EntityQueryDeriver extends DeriverBase implements ContainerDeriverInterface {
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
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content\ContentEntitySchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('typed_data_manager'),
      $container->get('graphql_content.schema_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    TypedDataManager $typedDataManager,
    ContentEntitySchemaConfig $schemaConfig
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typedDataManager = $typedDataManager;
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      if (!$this->schemaConfig->isEntityTypeExposed($id)) {
        continue;
      }

      if ($type instanceof ContentEntityTypeInterface) {
        $derivative = [
          'name' => graphql_propcase($id) . 'Query',
          'entity_type' => $id,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $definition = $this->typedDataManager->createDataDefinition("entity:$id");
        $properties = $definition->getPropertyDefinitions();

        $queryableProperties = array_filter($properties, function ($property) {
          return $property instanceof BaseFieldDefinition && $property->isQueryable();
        });

        if ($queryableProperties) {
          $derivative['arguments']['filter'] = [
            'multi' => FALSE,
            'nullable' => TRUE,
            'type' => graphql_camelcase([$id, 'query', 'filter', 'input']),
          ];
        }

        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
