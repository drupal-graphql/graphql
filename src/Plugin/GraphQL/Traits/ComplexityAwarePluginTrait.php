<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\GraphQL\ComplexFieldInterface;

trait ComplexityAwarePluginTrait {

  /**
   * {@inheritdoc}
   */
  abstract public function getPluginDefinition();

  /**
   * Build the field's cost for static query complexity analysis.
   *
   * @return int|callable
   *   The cost of this field or a callable to calculate the cost at runtime.
   */
  protected function buildCost() {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (isset($definition['cost'])) {
        return $definition['cost'];
      }
    }

    if ($this instanceof ComplexFieldInterface) {
      return [get_class($this), 'calculateCost'];
    }

    // The query processor has a default value of '1' for fields that do not
    // declare their cost by themselves.
    return NULL;
  }

}
