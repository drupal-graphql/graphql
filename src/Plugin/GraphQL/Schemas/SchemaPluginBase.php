<?php

namespace Drupal\graphql\Plugin\GraphQL\Schemas;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SchemaPluginBase extends PluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The schema builder service.
   *
   * @var \Drupal\graphql\Plugin\SchemaBuilder
   */
  protected $schemaBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('graphql.schema_builder')
    );
  }

  /**
   * SchemaPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\graphql\Plugin\SchemaBuilder $schemaBuilder
   *   The schema builder service.
   */
  public function __construct($configuration, $pluginId, $pluginDefinition, SchemaBuilder $schemaBuilder) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->schemaBuilder = $schemaBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $config = new SchemaConfig();

    if ($this->schemaBuilder->hasMutations()) {
      $config->setMutation(new ObjectType([
        'name' => 'MutationRoot',
        'fields' => function () {
          return $this->schemaBuilder->getMutations();
        },
      ]));
    }

    $config->setQuery(new ObjectType([
      'name' => 'QueryRoot',
      'fields' => function () {
        return $this->schemaBuilder->getFields('Root');
      },
    ]));

    $config->setTypes(function () {
      return $this->schemaBuilder->getTypes();
    });

    $config->setTypeLoader(function ($name) {
      return $this->schemaBuilder->getType($name);
    });

    return new Schema($config);
  }

}
