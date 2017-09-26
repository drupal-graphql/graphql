<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Extract Url objects from json paths.
 *
 * @GraphQLField(
 *   id = "json_path_entity",
 *   secure = true,
 *   name = "pathToEntity",
 *   type = "Entity",
 *   types = {"JsonObject", "JsonList"},
 *   arguments={
 *     "type" = {
 *       "type" = "String"
 *     },
 *     "steps" = {
 *       "type" = "String",
 *       "multi" = true
 *     }
 *   }
 * )
 */
class JsonPathToEntity extends JsonPath implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityRepositoryInterface $entityRepository
  ) {
    $this->entityRepository = $entityRepository;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }


  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues($value, $args, $info) as $item) {
      if ($entity = $this->entityRepository->loadEntityByUuid($args['type'], $item)) {
        if ($entity->access('view')) {
          yield $entity;
        }
      }
    }
  }

}