<?php

namespace Drupal\graphql_core;

use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Youshido\GraphQL\Schema\Schema;

/**
 * Generates a GraphQL Schema.
 */
class PluggableSchemaProvider implements SchemaProviderInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $pluginManager;

  /**
   * PluggableSchemaProvider constructor.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $pluginManager
   *   The graphql plugin manager.
   */
  public function __construct(GraphQLSchemaManagerInterface $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = new Schema();

    foreach ($this->pluginManager->getRootFields() as $field) {
      $schema->addQueryField($field);
    }

    foreach ($this->pluginManager->getMutations() as $mutation) {
      $schema->addMutationField($mutation);
    }

    $schema->getTypesList()->addTypes($this->pluginManager->find(function () {
      return TRUE;
    }, [
      GRAPHQL_CORE_TYPE_PLUGIN,
      GRAPHQL_CORE_INPUT_TYPE_PLUGIN,
    ]));

    return $schema;
  }

}
