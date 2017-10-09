<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Cache\CacheBackendInterface;
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
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface
   */
  protected $schemaManager;

  /**
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

    // Allow a custom field to be passed in.
    if (isset($field)) {
      $query->addField($field);
    }

    return new static(['schema' => [
      'query' => $query,
      'mutation' => $mutation,
      'types' => $types,
    ]], 'graphq:test', static::configuration());
  }
}
