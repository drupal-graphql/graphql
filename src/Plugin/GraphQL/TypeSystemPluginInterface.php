<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for GraphQL plugins.
 */
interface TypeSystemPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Plugin config builder method.
   *
   * During the build step, dependencies to other GraphQL plugins are supposed
   * to be resolved.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface $schemaManager
   *   The schema manager to resolve other plugins.
   */
  public function buildConfig(PluggableSchemaManagerInterface $schemaManager);

}
