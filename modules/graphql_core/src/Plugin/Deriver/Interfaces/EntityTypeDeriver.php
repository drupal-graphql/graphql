<?php

namespace Drupal\graphql_core\Plugin\Deriver\Interfaces;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive GraphQL Interfaces from Drupal entity types.
 */
class EntityTypeDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * EntityTypeDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Instance of an entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if (!$type instanceof ContentEntityTypeInterface) {
        continue;
      }

      $interfaces = isset($basePluginDefinition['interfaces']) ? $basePluginDefinition['interfaces'] : [];
      $interfaces = array_unique(array_merge($interfaces, $this->getInterfaces($type)));

      $this->derivatives[$typeId] = [
        'name' => StringHelper::camelCase($typeId),
        'description' => $this->t("The '@type' entity type.", [
          '@type' => $type->getLabel(),
        ]),
        'type' => "entity:$typeId",
        'interfaces' => $interfaces,
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Retrieve the interfaces that the entity type should implement.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $type
   *   The entity type to retrieve the interfaces for.
   *
   * @return array
   *   The interfaces that this entity type should implement.
   */
  protected function getInterfaces(EntityTypeInterface $type) {
    $pairs = [
      '\Drupal\Core\Entity\EntityDescriptionInterface' => 'EntityDescribable',
      '\Drupal\Core\Entity\EntityPublishedInterface' => 'EntityPublishable',
      '\Drupal\Core\Entity\RevisionableInterface' => 'EntityRevisionable',
      '\Drupal\user\EntityOwnerInterface' => 'EntityOwnable',
    ];

    $interfaces = [];
    foreach ($pairs as $dependency => $interface) {
      if ($type->entityClassImplements($dependency)) {
        $interfaces[] = $interface;
      }
    }

    return $interfaces;
  }

}
