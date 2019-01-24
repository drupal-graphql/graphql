<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;

/**
 * @Schema(
 *   id = "yml_config_schema",
 *   name = "YML config schema",
 *   context = {
 *     "graphql_server" = @ContextDefinition("entity:graphql_server", label =
 *   @Translation("GraphQL Server"))
 *   }
 * )
 * @codeCoverageIgnore
 */
class YamlConfigSdlSchema extends SdlSchemaPluginBase {

  protected $resolverRegistry;

  protected $resolverBuilder;

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return <<<GQL
      schema {
        query: Query
      }

      type Query {
        article(id: Int!): Article
        page(id: Int!): Page
        node(id: Int!): NodeInterface
        label(id: Int!): String
      }

      type Article implements NodeInterface {
        id: Int!
        uid: String
        title: String!
        render: String
      }

      type Page implements NodeInterface {
        id: Int!
        uid: String
        title: String
      }

      interface NodeInterface {
        id: Int!
      }
GQL;
  }

  /**
   * Return the schema config for the plugin to use.
   *
   * @return array
   */
  protected function getSchemaConfig() {
    return $this->configuration['schema_config'] ?? [];
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverRegistry|\Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected function getRegistry() {
    if (empty($this->resolverRegistry)) {
      $this->resolverRegistry = new ResolverRegistry([]);
    }
    return $this->resolverRegistry;
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverBuilder
   */
  protected function getBuilder() {
    if (empty($this->resolverBuilder)) {
      $this->resolverBuilder = new ResolverBuilder();
    }
    return $this->resolverBuilder;
  }

  /**
   * @param array $schema_config
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistry
   */
  protected function parseSchemaConfig(array $schema_config) {
    $registry = $this->getRegistry();
    foreach ($schema_config as $base_type => $config) {
      foreach ($config as $item) {
        if ($base_type == 'interfaces') {
          $registry->addTypeResolver($item['name'], $this->buildTypeResolver($item['id'], $item['arguments']));
        }
        if ($base_type == 'types') {
          foreach ($item['fields'] as $field) {
            $registry->addFieldResolver($item['name'], $field['name'], $this->buildFieldResolver($field));
          }
        }
      }
    }
    return $registry;
  }

  /**
   * @param $id
   * @param $config_item
   *
   * @return callable
   */
  protected function buildTypeResolver($id, $config_item) {
    $builder = $this->getBuilder();
    return $builder->produce($id, [
      'mapping' => $this->parseValueByArgs($id, $config_item['arguments'])
    ]);
  }

  protected function buildFieldResolver(array $field_config) {
    if (empty($field_config['value'])) {
      return function () {
      };
    }
    $value = $field_config['value'];
    if (is_array($value)) {
      $builder = $this->getBuilder();
      return $builder->produce($value['id'], [
        'mapping' => $this->parseValueByArgs($value['id'], $value['arguments'])
      ]);
    }
  }

  /**
   * @param $id
   * @param $arguments
   *
   * @return array
   */
  protected function parseValueByArgs($id, $arguments) {
    $builder = $this->getBuilder();
    $mapping = [];
    foreach ($arguments as $argument) {
//      if (is_array($argument['value'])) {
//        $mapping[$argument['name']] = $this->parseValueByArgs($argument['value']['id'], $argument['value']['arguments'] ?? []);
//      }
      if (is_string($argument['value'])) {
        if ($argument['name'] == 'parent') {
          $mapping[$argument['name']] = $builder->fromParent();
        }
        elseif ($argument['name'] == 'argument') {
          $mapping[$argument['name']] = $builder->fromArgument($argument['value']);
        }
        else {
          $mapping[$argument['name']] = $builder->fromValue($argument['value']);
        }
      }
    }
    return $mapping;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    if (!$config = $this->getSchemaConfig()) {
      return NULL;
    }
    return $this->parseSchemaConfig($config);
  }
}
