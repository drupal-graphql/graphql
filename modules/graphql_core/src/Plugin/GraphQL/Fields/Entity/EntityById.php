<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve an entity by its id.
 *
 * @GraphQLField(
 *   id = "entity_by_id",
 *   secure = true,
 *   arguments = {
 *     "id" = "String!"
 *   },
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityByIdDeriver"
 * )
 */
class EntityById extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository,
    EntityBuffer $entityBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityBuffer = $entityBuffer;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
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
      $container->get('entity.repository'),
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    $resolver = $this->entityBuffer->add($this->getPluginDefinition()['entity_type'], $args['id']);
    return function ($value, array $args, ResolveInfo $info) use ($resolver) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that the
        // cache entry is purged whenever a new entity of this type is saved.
        $pluginDefinition = $this->getPluginDefinition();
        $entityType = $this->entityTypeManager->getDefinition($pluginDefinition['entity_type']);
        yield (new CacheableValue(NULL))->addCacheTags($entityType->getListCacheTags());
      }
      else {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $access = $entity->access('view', NULL, TRUE);

        if ($access->isAllowed()) {
          if (isset($args['language']) && $args['language'] != $entity->language()->getId()) {
            $entity = $this->entityRepository->getTranslationFromContext($entity, $args['language']);
          }

          yield $entity->addCacheableDependency($access);
        }
        else {
          yield new CacheableValue(NULL, [$access]);
        }
      }
    };
  }

}
