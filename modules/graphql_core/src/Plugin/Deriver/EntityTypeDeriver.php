<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\graphql_content\ContentEntitySchemaConfig;

/**
 * Derive GraphQL Interfaces from Drupal entity types.
 */
class EntityTypeDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content\ContentEntitySchemaConfig
   */
  protected $schemaConfig;


  /**
   * EntityTypeDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Instance of an entity type manager.
   * @param \Drupal\graphql_content\ContentEntitySchemaConfig $schemaConfig
   *   The schema configuration service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ContentEntitySchemaConfig $schemaConfig
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('graphql_content.schema_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $this->derivatives[$typeId] = [
          'name' => StringHelper::camelCase($typeId),
          'data_type' => 'entity:' . $typeId,
          'entity_type' => $typeId,
        ] + $basePluginDefinition;
      }
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
