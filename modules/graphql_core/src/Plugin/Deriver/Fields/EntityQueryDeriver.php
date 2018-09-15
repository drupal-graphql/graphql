<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityQueryDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('typed_data_manager')
    );
  }

  /**
   * EntityQueryDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\TypedData\TypedDataManager $typedDataManager
   *   The typed data manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    TypedDataManager $typedDataManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $derivative = [
          'name' => StringHelper::propCase($id, 'query'),
          'description' => $this->t("Loads '@type' entities.", ['@type' => $type->getLabel()]),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        if ($id === 'node') {
          // TODO: Make this more generic.
          $derivative['response_cache_contexts'][] = 'user.node_grants:view';
        }

        $this->derivatives[$id] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
