<?php

namespace Drupal\graphql_core\Plugin\Deriver\Enums;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DisplayModeIdDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * DisplayModeIdDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->getDisplayModes() as $targetType => $displayModes) {
      $this->derivatives[$targetType] = [
        'name' => StringHelper::camelCase($targetType, 'display', 'mode', 'id'),
        'description' => $this->t("The available display modes for '@type' entities.", [
          '@type' => $this->entityTypeManager->getDefinition($targetType)->getLabel(),
        ]),
        'values' => $displayModes,
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Retrieves a list of entity view modes grouped by their target type.
   *
   * @return array
   *   The list of entity view modes grouped by the target entity type.
   */
  protected function getDisplayModes() {
    $storage = $this->entityTypeManager->getStorage('entity_view_mode');
    return array_reduce($storage->loadMultiple(), function ($carry, EntityViewModeInterface $current) {
      $target = $current->getTargetType();
      list(, $id) = explode('.', $current->id());

      $carry[$target][StringHelper::upperCase($id)] = [
        'value' => $id,
        'description' => $this->t("The '@label' display mode for '@type' entities.", [
          '@label' => $current->label(),
          '@type' => $this->entityTypeManager->getDefinition($target)->getLabel(),
        ]),
      ];

      return $carry;
    }, []);
  }

}
