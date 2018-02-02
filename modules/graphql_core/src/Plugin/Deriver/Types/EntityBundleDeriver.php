<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive GraphQL Interfaces from Drupal entity types.
 */
class EntityBundleDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

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
    $bundles = $this->entityTypeBundleInfo->getAllBundleInfo();
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if ($type instanceof ContentEntityTypeInterface && array_key_exists($typeId, $bundles)) {
        foreach ($bundles[$typeId] as $bundle => $bundleDefinition) {
          $this->derivatives[$typeId . '-' . $bundle] = [
            'name' => StringHelper::camelCase($typeId, $bundle),
            'description' => $this->t("The '@bundle' bundle of the '@type' entity type.", [
              '@bundle' => $bundleDefinition['label'],
              '@type' => $type->getLabel(),
            ]),
            'interfaces' => [StringHelper::camelCase($typeId)],
            'type' => "entity:$typeId:$bundle",
            'entity_type' => $typeId,
            'entity_bundle' => $bundle,
          ] + $basePluginDefinition;
        }
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
