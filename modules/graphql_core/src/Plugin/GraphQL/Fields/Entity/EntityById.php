<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_by_id",
 *   secure = true,
 *   arguments = {
 *     "id" = "String!"
 *   },
 *   contextual_arguments = {"language"},
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
   * EntityById constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
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
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $resolver = $this->entityBuffer->add($this->getPluginDefinition()['entity_type'], $args['id']);
    return function ($value, array $args, ResolveContext $context, ResolveInfo $info) use ($resolver) {
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
          if (isset($args['language']) && $args['language'] != $entity->language()->getId() && $entity instanceof TranslatableInterface && $entity->isTranslatable()) {
            if ($entity->hasTranslation($args['language'])) {
              $entity = $entity->getTranslation($args['language']);
            }
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
