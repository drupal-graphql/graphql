<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the entity result set of an entity query.
 *
 * @GraphQLField(
 *   id = "entity_query_entities",
 *   secure = true,
 *   name = "entities",
 *   type = "[Entity]",
 *   parents = {"EntityQueryResult"}
 * )
 */
class EntityQueryEntities extends FieldPluginBase implements ContainerFactoryPluginInterface {
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
   * EntityQueryEntities constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityBuffer $entityBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityBuffer = $entityBuffer;
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof QueryInterface) {
      $type = $value->getEntityTypeId();
      $result = $value->execute();

      if ($value->hasTag('revisions')) {
        return $this->resolveFromRevisionIds($type, array_keys($result));
      }

      // If this is a revision query, the version ids are the array keys.
      return $this->resolveFromEntityIds($type, array_values($result));
    }
  }

  /**
   * Resolves entities lazily through the entity buffer.
   *
   * @param string $type
   *   The entity type.
   * @param array $ids
   *   The entity ids to load.
   *
   * @return \Closure
   *   The deferred resolver.
   */
  protected function resolveFromEntityIds($type, $ids) {
    $resolve = $this->entityBuffer->add($type, $ids);
    return function($value, array $args, ResolveInfo $info) use ($resolve) {
      return $this->resolveEntities($resolve());
    };
  }

  /**
   * Resolves entity revisions.
   *
   * @param string $type
   *   The entity type.
   * @param array $ids
   *   The entity revision ids to load.
   *
   * @return \Generator
   *   The resolved revisions.
   */
  protected function resolveFromRevisionIds($type, $ids) {
    $storage = $this->entityTypeManager->getStorage($type);
    $entities = array_map(function ($id) use ($storage) {
      return $storage->loadRevision($id);
    }, $ids);

    return $this->resolveEntities($entities);
  }

  /**
   * Resolves entity objects and checks view permissions.
   *
   * @param array $entities
   *   The entities to resolve.
   *
   * @return \Generator
   *   The resolved entities.
   */
  protected function resolveEntities(array $entities) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    foreach ($entities as $entity) {
      $access = $entity->access('view', NULL, TRUE);

      if ($access->isAllowed()) {
        yield $entity->addCacheableDependency($access);
      }
      else {
        yield new CacheableValue(NULL, [$access]);
      }
    }
  }

}
