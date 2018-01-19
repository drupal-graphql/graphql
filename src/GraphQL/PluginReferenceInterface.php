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

}