<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create GraphQL entityById fields based on available Drupal entity types.
 */
class EntityByIdDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
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
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $derivative = [
          'name' => StringHelper::propCase($id, 'by', 'id'),
          'type' => StringHelper::camelCase($id),
          'description' => $this->t("Loads '@type' entities by their id.", ['@type' => $type->getLabel()]),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        if ($type->isTranslatable()) {
          $derivative['arguments']['language'] = [
            'type' => 'AvailableLanguages',
            'nullable' => TRUE,
          ];
        }

        $this->derivatives["entity:$id"] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
