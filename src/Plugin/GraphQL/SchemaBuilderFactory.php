<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginManagerInterface;

class SchemaBuilderFactory {

  /**
   * List of registered plugin managers.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface[]
   */
  protected $pluginManagers = [];

  /**
   * {@inheritdoc}
   */
  public function addPluginManager(PluginManagerInterface $pluginManager) {
    $this->pluginManagers[] = $pluginManager;
  }

  /**
   * Retrieve a schema builder instance.
   *
   * @param null $schemaReducer
   *   Schema reducer to apply to the schema.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaBuilder The schema builder.
   *   The schema builder.
   */
  public function getSchemaBuilder($schemaReducer = NULL) {
    // TODO: Implement schema reducer logic.
    return new SchemaBuilder($this->pluginManagers, $schemaReducer);
  }

}
