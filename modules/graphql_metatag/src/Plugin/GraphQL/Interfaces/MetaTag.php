<?php

namespace Drupal\graphql_metatag\Plugin\GraphQL\Interfaces;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The GraphQL type.
 *
 * @GraphQLInterface(
 *   id = "meta_tag",
 *   name = "MetaTag",
 * )
 */
class MetaTag extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The schema manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('graphql_core.schema_manager'));
  }

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
  public function resolveType($object) {
    if (is_array($object) && array_key_exists('type', $object)) {
      return $this->schemaManager->findByName($object['type'], [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
  }

}
