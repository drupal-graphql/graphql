<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Field\AbstractField;

/**
 * Empty test schema used by SchemaProphecyTrait.
 */
class TestSchema extends SchemaPluginBase implements SchemaPluginInterface {

  /**
   * Mocked plugin configuration.
   *
   * @return array
   */
  public static function pluginDefinition() {
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
    $schema = new static(['field' => $field], 'graphql:test', static::pluginDefinition());
    $schema->buildConfig(new PluggableSchemaBuilder($container->get('graphql.plugin_manager_aggregator')));
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function constructSchema(SchemaBuilderInterface $schemaBuilder) {
    parent::constructSchema($schemaBuilder);

    // Allow injection of an additional field.
    if (!empty($this->configuration['field'])) {
      $this->getQueryType()->addField($this->configuration['field']);
    }
  }

}
