<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Schema\InternalSchemaMutationObject;
use Youshido\GraphQL\Schema\InternalSchemaQueryObject;

/**
 * Empty test schema used by SchemaProphecyTrait.
 */
class TestSchema extends SchemaPluginBase implements SchemaPluginInterface {

  /**
   * The pluggable schema manager service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaBuilder
   */
  protected $schemaManager;

  /**
   * Mocked plugin configuration.
   *
   * @return array
   */
  public static function configuration() {
    return [
      'name' => 'default',
      'path' => 'graphql',
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, AbstractField $field = NULL) {
    /** @var \Drupal\graphql\Plugin\GraphQL\SchemaBuilderFactory $schemaBuilderFactory */
    $schemaBuilderFactory = $container->get('graphql.schema_builder_factory');
    $schemaBuilder = $schemaBuilderFactory->getSchemaBuilder();

    $mutation = new InternalSchemaMutationObject(['name' => 'RootMutation']);
    $mutation->addFields($schemaBuilder->getMutations());

    $query = new InternalSchemaQueryObject(['name' => 'RootQuery']);
    $query->addFields($schemaBuilder->getRootFields());

    // Allow injection of an additional field.
    if (!empty($field)) {
      $query->addField($field);
    }

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

    return new static(['schema' => $schema], 'graphql:test', static::configuration());
  }

  /**
   * {@inheritdoc}
   */
  protected function constructSchema($configuration, $pluginId, $pluginDefinition) {
    $this->config = new SchemaConfig($configuration['schema']);
  }
}
