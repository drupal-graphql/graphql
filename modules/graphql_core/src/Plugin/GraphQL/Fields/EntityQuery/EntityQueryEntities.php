<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof QueryInterface) {
      $storage = $this->entityTypeManager->getStorage($value->getEntityTypeId());

      $ids = $value->execute();
      $entities = array_filter($storage->loadMultiple($ids), function(ContentEntityInterface $entity) {
        return $entity->access('view');
      });

      foreach ($entities as $result) {
        yield $result;
      }
    }
  }

}
