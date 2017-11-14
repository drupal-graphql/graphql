<?php

namespace Drupal\Tests\graphql\Traits;

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
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, AbstractField $field = NULL) {
    return new static(['field' => $field], 'graphql:test', static::pluginDefinition(), $container->get('graphql.plugin_manager_aggregator'));
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    if (!isset($this->schema)) {
      $schema = parent::getSchema();

      // Allow injection of an additional field.
      if (!empty($this->configuration['field'])) {
        $schema->getQueryType()->addField($this->configuration['field']);
      }

      $this->schema = $schema;
    }

    return $this->schema;
  }

}
