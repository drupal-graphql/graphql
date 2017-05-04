<?php

namespace Drupal\graphql_core\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Field\InputField;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Methods for argument aware plugins.
 */
trait ArgumentAwarePluginTrait {
  use TypedPluginTrait;

  /**
   * Build the arguments list.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\InputFieldInterface[]
   *   The list of arguments.
   */
  protected function buildArguments(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (!$definition['arguments']) {
        return [];
      }
      $arguments = [];
      if ($definition['arguments']) {
        foreach ($definition['arguments'] as $name => $argument) {
          $typeinfo = is_array($argument) ? $argument['type'] : $argument;
          $type = is_array($typeinfo) ? $this->buildEnumConfig($typeinfo) : $schemaManager->findByName($typeinfo, [
            GRAPHQL_CORE_INPUT_TYPE_PLUGIN,
            GRAPHQL_CORE_SCALAR_PLUGIN,
            GRAPHQL_CORE_ENUM_PLUGIN,
          ]);
          if ($type instanceof TypeInterface) {
            $arguments[$name] = new InputField([
              'name' => $name,
              'type' => $this->decorateType(
                $type,
                is_array($argument) && array_key_exists('nullable', $argument) && $argument['nullable'],
                is_array($argument) && array_key_exists('multi', $argument) && $argument['multi']
              ),
            ]);
          }
        }
      }
      return $arguments;
    }
    return [];
  }

}
