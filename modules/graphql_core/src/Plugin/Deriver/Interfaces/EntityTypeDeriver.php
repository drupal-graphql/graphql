<?php

namespace Drupal\graphql_core\Plugin\Deriver\Interfaces;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
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
    $this->derivatives = [];
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $this->derivatives[$typeId] = [
          'name' => StringHelper::camelCase($typeId),
          'description' => $this->t("The '@type' entity type.", [
            '@type' => $type->getLabel(),
          ]),
          'data_type' => 'entity:' . $typeId,
          'entity_type' => $typeId,
        ] + $basePluginDefinition;
      }
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
