<?php

namespace Drupal\graphql\Plugin\GraphQL;

interface PluggableSchemaPluginInterface extends SchemaPluginInterface {

  /**
   * Retrieves the schema builder.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface
   *   The schema builder.
   */
  public function getSchemaBuilder();

}
