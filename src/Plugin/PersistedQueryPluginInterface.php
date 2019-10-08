<?php


namespace Drupal\graphql\Plugin;

interface PersistedQueryPluginInterface {

  /**
   * @return string
   *   The actual GraphQL query.
   */
  public function getQuery();
}
