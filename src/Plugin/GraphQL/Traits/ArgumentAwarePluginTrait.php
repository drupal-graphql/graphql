<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\graphql\Utility\StringHelper;

trait ArgumentAwarePluginTrait {

  /**
   * Builds the list of arguments.
   *
   * @param array $definition
   *   The plugin definition array.
   *
   * @return array
   *   The list of arguments.
   */
  protected function buildArguments($definition) {
    return array_map(function ($argument) use ($definition) {
      return [
        'optional' => !empty($argument['optional']),
        'type' => $this->buildArgumentType($argument, $definition),
        'description' => $this->buildArgumentDescription($argument, $definition),
        'defaultValue' => $this->buildArgumentDefault($argument, $definition),
      ];
    }, $definition['arguments']);
  }

  /**
   * Builds an argument's type.
   *
   * @param mixed $argument
   *   The argument definition.
   *
   * @return array
   *   The pre-parsed type definition of the argument.
   */
  protected function buildArgumentType($argument) {
    $type = is_array($argument) ? $argument['type'] : $argument;
    return StringHelper::parseType($type);
  }

  /**
   * Builds an argument's description.
   *
   * @param mixed $argument
   *   The argument definition.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return string
   *   The description of the argument.
   */
  protected function buildArgumentDescription($argument, $definition) {
    return (string) (isset($argument['description']) ? $argument['description'] : '');
  }

  /**
   * Builds an argument's default value.
   *
   * @param mixed $argument
   *   The argument definition.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return mixed
   *   The argument's default value.
   */
  protected function buildArgumentDefault($argument, $definition) {
    return isset($argument['default']) ? $argument['default'] : NULL;
  }

}
