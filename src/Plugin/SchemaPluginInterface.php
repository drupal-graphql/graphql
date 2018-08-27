<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves the schema.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  public function getSchema();

  /**
   * Validates the schema.
   *
   * @return null|array
   */
  public function validateSchema();

  /**
   * @return mixed
   */
  public function getServer();

}
