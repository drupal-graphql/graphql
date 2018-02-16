<?php

namespace Drupal\graphql\Plugin\GraphQL\Schemas;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SchemaPluginBase extends PluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The schema builder object.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface
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
      $container->get('graphql.plugin_manager_aggregator')
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
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator $pluginManagers
   *   Type system plugin manager aggregator service.
   */
  public function __construct($configuration, $pluginId, $pluginDefinition, TypeSystemPluginManagerAggregator $pluginManagers) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->schemaBuilder = new PluggableSchemaBuilder($pluginManagers);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $config = new SchemaConfig();

    if ($mutations = $this->getMutations()) {
      $config->setMutation(new ObjectType([
        'name' => 'MutationRoot',
        'fields' => function () use ($mutations) {
          return $this->schemaBuilder->resolveFields($mutations);
        }
      ]));
    }

    if ($query = $this->getRootFields()) {
      $config->setQuery(new ObjectType([
        'name' => 'QueryRoot',
        'fields' => function () use ($query) {
          return $this->schemaBuilder->resolveFields($query);
        }
      ]));
    }

    $config->setTypes(function () {
      return $this->getTypes();
    });

    $config->setTypeLoader(function ($name) {
      return $this->schemaBuilder->getTypeByName($name);
    });

    return new Schema($config);
  }

  /**
   * Extract type or field definitions from plugins.
   *
   * @param array $plugins
   *   The list of plugins to extract the type or field definitions from.
   *
   * @return array
   *   The list of extracted type or field definitions.
   */
  protected function extractDefinitions(array $plugins) {
    return array_filter(array_map(function (TypeSystemPluginInterface $plugin) {
      return $plugin->getDefinition($this->schemaBuilder);
    }, $plugins));
  }

  /**
   * Retrieve all mutations.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of mutation plugins.
   */
  protected function getMutations() {
    return $this->schemaBuilder->getMutationMap();
  }

  /**
   * Retrieve all fields that are not associated with a specific type.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list root field plugins.
   */
  protected function getRootFields() {
    return $this->getFields('Root');
  }

  /**
   * Retrieve all fields that are not associated with a specific type.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list root field plugins.
   */
  protected function getFields($type) {
    $map = $this->schemaBuilder->getFieldMap();
    return isset($map[$type]) ? $map[$type] : [];
  }

  /**
   * Retrieve all types to be registered explicitly.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of types to be registered explicitly.
   */
  protected function getTypes() {
    return array_filter(array_map(function ($type) {
      return $this->schemaBuilder->getTypeByName($type);
    }, array_keys($this->schemaBuilder->getTypeMap())));
  }

}
