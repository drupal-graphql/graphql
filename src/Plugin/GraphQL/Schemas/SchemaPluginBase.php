<?php

namespace Drupal\graphql\Plugin\GraphQL\Schemas;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Schema\Schema;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerAggregator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Schema\InternalSchemaMutationObject;
use Youshido\GraphQL\Schema\InternalSchemaQueryObject;

abstract class SchemaPluginBase extends PluginBase implements PluggableSchemaPluginInterface, ContainerFactoryPluginInterface {

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
  public function getSchemaBuilder() {
    return $this->schemaBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $mutation = new InternalSchemaMutationObject(['name' => 'RootMutation']);
    $mutation->addFields($this->extractDefinitions(($this->getMutations())));

    $query = new InternalSchemaQueryObject(['name' => 'RootQuery']);
    $query->addFields($this->extractDefinitions($this->getRootFields()));

    $types = $this->extractDefinitions($this->getTypes());

    return new Schema($this, [
      'query' => $query,
      'mutation' => $mutation,
      'types' => $types,
    ]);
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
    return array_map(function (TypeSystemPluginInterface $plugin) {
      return $plugin->getDefinition($this->schemaBuilder);
    }, $plugins);
  }

  /**
   * Retrieve all mutations.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of mutation plugins.
   */
  protected function getMutations() {
    return $this->schemaBuilder->find(function() {
      return TRUE;
    }, [GRAPHQL_MUTATION_PLUGIN]);
  }

  /**
   * Retrieve all fields that are not associated with a specific type.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list root field plugins.
   */
  protected function getRootFields() {
    // Retrieve the list of fields that are not attached to any type or are
    // explicitly attached to the artificial "Root" type.
    return $this->schemaBuilder->find(function($definition) {
      return empty($definition['parents']) || in_array('Root', $definition['parents']);
    }, [GRAPHQL_FIELD_PLUGIN]);
  }

  /**
   * Retrieve all types to be registered explicitly.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface[]
   *   The list of types to be registered explicitly.
   */
  protected function getTypes() {
    return $this->schemaBuilder->find(function() {
      return TRUE;
    }, [
      GRAPHQL_UNION_TYPE_PLUGIN,
      GRAPHQL_TYPE_PLUGIN,
      GRAPHQL_INPUT_TYPE_PLUGIN,
    ]);
  }

}
