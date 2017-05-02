<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Interfaces;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLInterface(
 *   id = "entity",
 *   name = "Entity",
 *   fields = {
 *     "entityId",
 *     "entityUuid",
 *     "entityLabel",
 *     "entityType",
 *     "entityBundle",
 *     "entityUrl"
 *   }
 * )
 */
class Entity extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * A schema manager instance.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, GraphQLSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('graphql_core.schema_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof EntityInterface) {
      $type = graphql_core_camelcase([$object->getEntityTypeId(), $object->bundle()]);
      return $this->schemaManager->findByName($type, [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
    return NULL;
  }

}