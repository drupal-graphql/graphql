<?php

namespace Drupal\graphql_config\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql_config\ConfigEntitySchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL entityById fields based on available Drupal entity types.
 */
class EntityByIdDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_config\ConfigEntitySchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('graphql_config.schema_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ConfigEntitySchemaConfig $schemaConfig
  ) {
    $this->entityTypeManager = $entityTypeManager;
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
      if ($type instanceof ConfigEntityTypeInterface) {
        $derivative = [
          'name' => graphql_propcase($id) . 'ById',
          'type' => graphql_camelcase($id),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        if ($type->isTranslatable()) {
          $derivative['arguments']['language'] = 'AvailableLanguages';
        }

        $this->derivatives["entity:$id"] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
