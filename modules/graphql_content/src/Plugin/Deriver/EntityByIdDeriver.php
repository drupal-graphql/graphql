<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\qraphql_content\Traits\GraphQLEntityExposeTrait;

/**
 * Create GraphQL entityById fields based on available Drupal entity types.
 */
class EntityByIdDeriver extends DeriverBase implements ContainerDeriverInterface {
  use GraphQLEntityExposeTrait;

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
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $type) {
      if (!$this->isEntityTypeExposed($id)) {
        continue;
      }
      if ($type instanceof ContentEntityTypeInterface) {
        $derivative = [
          'name' => StringHelper::propCase([$id, 'by', 'id']),
          'type' => StringHelper::camelCase($id),
          'entity_type' => $id,
        ] + $basePluginDefinition;

        if ($type->isTranslatable()) {
          $derivative['arguments']['language'] = 'AvailableLanguages';
        }

        $this->derivatives["entity:$id"] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
