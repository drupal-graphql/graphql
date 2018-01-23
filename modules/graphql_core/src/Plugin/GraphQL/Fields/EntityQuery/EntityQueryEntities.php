<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Batching\Buffers\EntityBuffer;
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
 *   type = "Entity",
 *   parents = {"EntityQueryResult"},
 *   multi = true,
 *   nullable = true
 * )
 */
class EntityQueryEntities extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Batching\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityBuffer $entityBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityBuffer = $entityBuffer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof QueryInterface) {
      $resolve = $this->entityBuffer->add($value->getEntityTypeId(), $value->execute());
      return function($value, array $args, ResolveInfo $info) use ($resolve) {
        $entities = $resolve();

        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        foreach ($entities as $entity) {
          if (($access = $entity->access('view', NULL, TRUE)) && $access->isAllowed()) {
            yield new CacheableValue($entity, [$access]);
          }
          else {
            yield new CacheableValue(NULL, [$access]);
          }
        }
      };
    }
  }

}
