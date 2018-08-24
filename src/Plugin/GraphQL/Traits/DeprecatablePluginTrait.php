<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

trait DeprecatablePluginTrait {

  /**
   * @param $definition
   *
   * @return null
   */
  protected function buildDeprecationReason($definition) {
    return !empty($definition['deprecated']) ? $definition['deprecated'] : NULL;
  }

}
