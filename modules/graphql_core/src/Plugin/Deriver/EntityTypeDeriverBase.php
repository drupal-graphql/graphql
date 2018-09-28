<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class EntityTypeDeriverBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
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
   * EntityTypeDeriver constructor.
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
   * Retrieve the interfaces that the entity type should implement.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $type
   *   The entity type to retrieve the interfaces for.
   * @param array $basePluginDefinition
   *   The base plugin definition array.
   *
   * @return array
   *   The interfaces that this entity type should implement.
   */
  protected function getInterfaces(EntityTypeInterface $type, array $basePluginDefinition) {
    $pairs = [
      '\Drupal\Core\Entity\EntityDescriptionInterface' => 'EntityDescribable',
      '\Drupal\Core\Entity\EntityPublishedInterface' => 'EntityPublishable',
      '\Drupal\user\EntityOwnerInterface' => 'EntityOwnable',
    ];

    $interfaces = isset($basePluginDefinition['interfaces']) ? $basePluginDefinition['interfaces'] : [];
    $interfaces[] = 'Entity';

    foreach ($pairs as $dependency => $interface) {
      if ($type->entityClassImplements($dependency)) {
        $interfaces[] = $interface;
      }
    }

    if ($type->isRevisionable()) {
      $interfaces[] = 'EntityRevisionable';
    }

    return array_unique($interfaces);
  }

}
