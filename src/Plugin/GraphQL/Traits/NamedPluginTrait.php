<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Methods for named plugins.
 */
trait NamedPluginTrait {

  /**
   * Build the fields name.
   *
   * @return string
   *   The field name string.
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
   * Build the fields description.
   *
   * @return string
   *   The field description string.
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
