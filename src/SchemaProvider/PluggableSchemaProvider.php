<?php

namespace Drupal\graphql\SchemaProvider;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface;
use Youshido\GraphQL\Schema\Schema;

/**
 * Generates a GraphQL Schema.
 */
class PluggableSchemaProvider implements SchemaProviderInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface
   */
  protected $pluginManager;

  /**
   * PluggableSchemaProvider constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface $pluginManager
   *   The graphql plugin manager.
   */
  public function __construct(PluggableSchemaManagerInterface $pluginManager) {
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

    $schema->getTypesList()->addTypes(array_filter($this->pluginManager->find(function() {
      return TRUE;
    }, [
      GRAPHQL_UNION_TYPE_PLUGIN,
      GRAPHQL_TYPE_PLUGIN,
      GRAPHQL_INPUT_TYPE_PLUGIN,
    ])));

    return $schema;
  }

}
