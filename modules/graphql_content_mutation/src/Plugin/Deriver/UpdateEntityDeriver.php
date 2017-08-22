<?php

namespace Drupal\graphql_content_mutation\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateEntityDeriver extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('graphql_content_mutation.schema_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityTypeManagerInterface $entityTypeManager,
    ContentEntityMutationSchemaConfig $schemaConfig
  ) {
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityTypeManager = $entityTypeManager;
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      foreach ($this->entityTypeBundleInfo->getBundleInfo($entityTypeId) as $bundleName => $bundle) {
        if (!$this->schemaConfig->exposeUpdate($entityTypeId, $bundleName)) {
          continue;
        }

        $this->derivatives["$entityTypeId:$bundleName"] = [
          'name' => 'update' . graphql_camelcase([$entityTypeId, $bundleName]),
          'arguments' => [
            'id' => [
              'type' => 'String',
              'nullable' => FALSE,
              'multi' => FALSE,
            ],
            'input' => [
              'type' => graphql_camelcase([$entityTypeId, $bundleName]) . 'UpdateInput',
              'nullable' => FALSE,
              'multi' => FALSE,
            ],
          ],
          'entity_type' => $entityTypeId,
          'entity_bundle' => $bundleName,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
