<?php

namespace Drupal\graphql\GraphQL;

interface PluginReferenceInterface {

  /**
   * Retrieves the referenced plugin instance.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The referenced plugin instance.
   */
  public function getPlugin();

  /**
   * Retrieves the plugin definition of the referenced plugin.
   *
   * @return mixed
   *   The referenced plugin's definition.
   */
  public function getPluginDefinition();

  /**
   * Retrieves the plugin id of the referenced plugin.
   *
   * @return string
   *   The referenced plugin's id.
   */
  public function getPluginId();

}