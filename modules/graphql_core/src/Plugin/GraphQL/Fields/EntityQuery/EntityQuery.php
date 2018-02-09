<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_query",
 *   secure = true,
 *   type = "EntityQueryResult!",
 *   arguments = {
 *     "offset" = {
 *       "type" = "Int",
 *       "default" = 0
 *     },
 *     "limit" = {
 *       "type" = "Int",
 *       "default" = 10
 *     },
 *     "revisions" = {
 *       "type" = "EntityQueryRevisionMode",
 *       "default" = "default"
 *     }
 *   },
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityQueryDeriver"
 * )
 */
class EntityQuery extends FieldPluginBase implements ContainerFactoryPluginInterface {
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
  protected function getCacheDependencies(array $result, $value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $type = $this->entityTypeManager->getDefinition($entityTypeId);

    $metadata = new CacheableMetadata();
    $metadata->addCacheTags($type->getListCacheTags());
    $metadata->addCacheContexts($type->getListCacheContexts());

    return [$metadata];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $this->getQuery($value, $args, $info);
  }

  /**
   * Create an entity query for the plugin's entity type.
   *
   * @param mixed $value
   *   The parent entity type.
   * @param array $args
   *   The field arguments array.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The entity query object.
   */
  protected function getQuery($value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $entityStorage = $this->entityTypeManager->getStorage($entityTypeId);
    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);

    $query = $entityStorage->getQuery();
    $query->range($args['offset'], $args['limit']);
    $query->sort($entityType->getKey('id'));
    $query->accessCheck(TRUE);

    // Check if this is a query for all entity revisions.
    if (!empty($args['revisions']) && $args['revisions'] === 'all') {
      // Mark the query as such and sort by the revision id too.
      $query->allRevisions();
      $query->addTag('revisions');
      $query->sort($entityType->getKey('revision'));
    }

    if (!empty($args['filter'])) {
      /** @var \Youshido\GraphQL\Type\Object\AbstractObjectType $filter */
      $filter = $info->getField()->getArgument('filter')->getType();
      /** @var \Drupal\graphql\GraphQL\Type\InputObjectType $filterType */
      $filterType = $filter->getNamedType();
      $filterFields = $filterType->getPlugin()->getPluginDefinition()['fields'];

      foreach ($args['filter'] as $key => $arg) {
        $query->condition($filterFields[$key]['field_name'], $arg);
      }
    }

    return $query;
  }

}
