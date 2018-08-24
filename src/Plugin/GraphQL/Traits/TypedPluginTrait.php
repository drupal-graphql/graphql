<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\graphql\Utility\StringHelper;

trait TypedPluginTrait {

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildType($definition) {
    return StringHelper::parseType($definition['type']);
  }

}
