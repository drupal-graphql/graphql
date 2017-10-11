<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Schemas;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\InternalSchemaMutationObject;
use Youshido\GraphQL\Schema\InternalSchemaQueryObject;

/**
 * Default generated schema.
 *
 * @GraphQLSchema(
 *   id = "default",
 *   name = "Default",
 *   path = "/graphql"
 * )
 */
class DefaultSchema extends SchemaPluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The pluggable schema manager service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface $schemaManager */
    $schemaManager = $container->get('graphql.pluggable_schema_manager');

    $query = new InternalSchemaQueryObject(['name' => 'RootQuery']);
    $query->addFields($schemaManager->getRootFields());

    $mutation = new InternalSchemaMutationObject(['name' => 'RootMutation']);
    $mutation->addFields($schemaManager->getMutations());

    $types = $schemaManager->find(function() {
      return TRUE;
    }, [
      GRAPHQL_UNION_TYPE_PLUGIN,
      GRAPHQL_TYPE_PLUGIN,
      GRAPHQL_INPUT_TYPE_PLUGIN,
    ]);

    return new static($configuration + ['schema' => [
      'query' => $query,
      'mutation' => $mutation,
      'types' => $types,
    ]], $plugin_id, $plugin_definition);
  }
}
