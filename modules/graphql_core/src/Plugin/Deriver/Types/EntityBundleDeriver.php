<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive GraphQL Interfaces from Drupal entity types.
 */
class EntityBundleDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Bundle info manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * EntityBundleDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Instance of an entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   Instance of the entity bundle info service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];
    $bundles = $this->entityTypeBundleInfo->getAllBundleInfo();
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if ($type instanceof ContentEntityTypeInterface && array_key_exists($typeId, $bundles)) {
        foreach (array_keys($bundles[$typeId]) as $bundle) {
          $this->derivatives[$typeId . '-' . $bundle] = [
            'name' => EntityBundle::getId($typeId, $bundle),
            'entity_type' => $typeId,
            'data_type' => 'entity:' . $typeId . ':' . $bundle,
            'interfaces' => [StringHelper::camelCase($typeId)],
            'bundle' => $bundle,
          ] + $basePluginDefinition;
        }
      }
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
