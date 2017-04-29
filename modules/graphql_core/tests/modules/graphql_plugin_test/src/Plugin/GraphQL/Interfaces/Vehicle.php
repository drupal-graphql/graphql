<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Interfaces;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Vehicle interface definition.
 *
 * @GraphQLInterface(
 *   name = "Vehicle",
 *   fields = { "type", "wheels" }
 * )
 */
class Vehicle extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The plugin manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    return $this->schemaManager->findByName($object['type'], [GRAPHQL_CORE_TYPE_PLUGIN]);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GraphQLSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('graphql.schema_manager'));
  }

}
