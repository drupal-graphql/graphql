<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Interfaces;


use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * GraphQL interface for JSON objects.
 *
 * @GraphQLInterface(
 *   id = "json_node",
 *   name = "JsonNode"
 * )
 */
class JsonNode extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The GraphQL schema manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

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
      $container->get('graphql_core.schema_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    GraphQLSchemaManagerInterface $schemaManager
  ) {
    $this->schemaManager = $schemaManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }


  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if (!is_array($object)) {
      return $this->schemaManager->findByName('JsonLeaf', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
    else {
      if (count(array_filter(array_keys($object), 'is_string')) > 0) {
        return $this->schemaManager->findByName('JsonObject', [GRAPHQL_CORE_TYPE_PLUGIN]);
      }
      else {
        return $this->schemaManager->findByName('JsonList', [GRAPHQL_CORE_TYPE_PLUGIN]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return !is_object($value);
  }


}