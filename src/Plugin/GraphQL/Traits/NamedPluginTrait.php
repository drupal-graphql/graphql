<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Methods for named plugins.
 */
trait NamedPluginTrait {

  /**
   * Build the plugin's name.
   *
   * @return string
   *   The plugin name string.
   */
  protected function buildName() {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('name', $definition)) {
        return $definition['name'];
      }
    }

    return NULL;
  }

  /**
   * Build the plugin's description.
   *
   * @return string
   *   The plugin description string.
   */
  protected function buildDescription() {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('description', $definition)) {
        return $definition['description'];
      }
    }

    return NULL;
  }

}
