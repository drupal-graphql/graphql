<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\graphql\Utility\StringHelper;

trait ArgumentAwarePluginTrait {

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildArguments($definition) {
    return array_map(function ($argument) use ($definition) {
      return [
        'type' => $this->buildArgumentType($argument, $definition),
        'description' => $this->buildArgumentDescription($argument, $definition),
        'defaultValue' => $this->buildArgumentDefault($argument, $definition),
      ];
    }, $definition['arguments']);
  }

  /**
   * @param $argument
   *
   * @return array
   */
  protected function buildArgumentType($argument) {
    $type = is_array($argument) ? $argument['type'] : $argument;
    return StringHelper::parseType($type);
  }

  /**
   * @param $argument
   * @param $definition
   *
   * @return string
   */
  protected function buildArgumentDescription($argument, $definition) {
    return (string) (isset($argument['description']) ? $argument['description'] : '');
  }

  /**
   * @param $argument
   * @param $definition
   *
   * @return null
   */
  protected function buildArgumentDefault($argument, $definition) {
    return isset($argument['default']) ? $argument['default'] : NULL;
  }

}
