<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\TypedData\TypedDataManager;
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
  public function __construct(EntityTypeManagerInterface $entityTypeManager, TypedDataManager $typedDataManager) {
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
          'name' => graphql_core_propcase($id) . 'Query',
          'type' => graphql_core_camelcase($id),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
        $definition = $this->typedDataManager->createDataDefinition("entity:$id");
        $properties = $definition->getPropertyDefinitions();

        // Add all queryable properties as args.
        foreach ($properties as $key => $property) {
          if ($property instanceof BaseFieldDefinition && $property->isQueryable()) {
            $argName = graphql_core_propcase($key);

            // Some args are predefined (e.g. 'offset' and 'limit'). Don't
            // override those.
            if (isset($derivative['arguments'][$argName])) {
              continue;
            }

            $mainProperty = $property->getMainPropertyName();
            $mainPropertyDataType = $property->getPropertyDefinition($mainProperty)->getDataType();

            $derivative['arguments'][$argName] = [
              'multi' => FALSE,
              'nullable' => TRUE,
              'data_type' => $mainPropertyDataType,
            ];
          }
        }

        $this->derivatives["entity:$id"] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
