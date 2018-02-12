<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

      // Create the entity type only for types that do not support bundles. For
      // all others, we create common interfaces instead.
      if ($type->hasKey('bundle')) {
        continue;
      }

      $this->derivatives[$typeId] = [
        'name' => StringHelper::camelCase($typeId),
        'description' => $this->t("The '@type' entity type.", [
          '@type' => $type->getLabel(),
        ]),
        'type' => "entity:$typeId",
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
