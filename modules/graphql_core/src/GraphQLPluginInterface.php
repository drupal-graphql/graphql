<?php

namespace Drupal\graphql_core;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for GraphQL plugins.
 */
interface GraphQLPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Plugin config builder method.
   *
   * During the build step, dependencies to other GraphQL plugins are supposed
   * to be resolved.
   *
   * @param GraphQLSchemaManagerInterface $schemaManager
   *   The schema manager to resolve other plugins.
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager);

}
