<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve an entity by its id.
 *
 * @GraphQLField(
 *   id = "entity_by_id",
 *   name = "entityById",
 *   nullable = true,
 *   multi = false,
 *   weight = -1,
 *   arguments = {
 *     "id" = "String"
 *   },
 *   deriver = "\Drupal\graphql_content\Plugin\Deriver\EntityByIdDeriver"
 * )
 */
class EntityById extends FieldPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, EntityRepositoryInterface $entityRepository) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    $storage = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type']);

    if (($entity = $storage->load($args['id'])) && $entity->access('view')) {
      if (isset($args['language']) && $args['language'] != $entity->language()->getId()) {
        $entity = $this->entityRepository->getTranslationFromContext($entity, $args['language']);
      }

      yield $entity;
    }
  }

}
