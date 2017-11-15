<?php

namespace Drupal\graphql\Plugin\GraphQL;

interface TypeSystemPluginReferenceInterface {

  /**
   * Retrieves the referenced plugin instance.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   The schema builder.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The referenced plugin instance.
   */
  public function getPlugin(PluggableSchemaBuilderInterface $schemaBuilder);

}