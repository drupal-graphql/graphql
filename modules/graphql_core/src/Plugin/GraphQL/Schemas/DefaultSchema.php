<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Schemas;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
class DefaultSchema extends SchemaPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\graphql\Plugin\GraphQL\SchemaBuilderFactory $schemaBuilderFactory */
    $schemaBuilderFactory = $container->get('graphql.schema_builder_factory');
    // TODO: Inject schema reducer configuration into the schema builder.
    $schemaBuilder = $schemaBuilderFactory->getSchemaBuilder();

    $mutation = new InternalSchemaMutationObject(['name' => 'RootMutation']);
    $mutation->addFields($schemaBuilder->getMutations());

    $query = new InternalSchemaQueryObject(['name' => 'RootQuery']);
    $query->addFields($schemaBuilder->getRootFields());

    $types = $schemaBuilder->find(function() {
      return TRUE;
    }, [
      GRAPHQL_UNION_TYPE_PLUGIN,
      GRAPHQL_TYPE_PLUGIN,
      GRAPHQL_INPUT_TYPE_PLUGIN,
    ]);

    $schema = [
      'query' => $query,
      'mutation' => $mutation,
      'types' => $types,
    ];

    return new static(
      $configuration + ['schema' => $schema],
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function constructSchema($configuration, $pluginId, $pluginDefinition) {
    $this->config = new SchemaConfig($configuration['schema']);
  }
}
