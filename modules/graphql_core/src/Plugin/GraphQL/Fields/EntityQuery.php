<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of entities through an entity query.
 *
 * @GraphQLField(
 *   id = "entity_query",
 *   name = "entityQuery",
 *   nullable = false,
 *   multi = true,
 *   weight = -1,
 *   arguments = {
 *     "offset" = {
 *       "type" = "Int",
 *       "nullable" = true,
 *       "default" = 0
 *     },
 *     "limit" = {
 *       "type" = "Int",
 *       "nullable" = true,
 *       "default" = 10
 *     }
 *   },
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\EntityQueryDeriver"
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
  public function resolveValues($value, array $args, ResolveInfo $info) {
    $storage = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type']);
    $type = $this->entityTypeManager->getDefinition($this->pluginDefinition['entity_type']);

    $query = $storage->getQuery();
    $query->range($args['offset'], $args['limit']);
    $query->sort($type->getKey('id'));

    foreach (array_diff_key($args, array_flip(['offset', 'limit'])) as $key => $arg) {
      $query->condition($key, $arg);
    }

    $ids = $query->execute();
    $entities = array_filter($storage->loadMultiple($ids), function (ContentEntityInterface $entity) {
      return $entity->access('view');
    });

    foreach ($entities as $entity) {
      yield $entity;
    }
  }

}
