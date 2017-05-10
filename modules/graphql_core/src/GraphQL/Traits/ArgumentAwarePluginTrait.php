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
      foreach ($definition['arguments'] as $name => $argument) {
        $type = $this->buildArgumentType($schemaManager, $argument);

        if ($type instanceof TypeInterface) {
          $config = [
            'name' => $name,
            'type' => $type,
          ];

          if (is_array($argument) && isset($argument['default'])) {
            $config['defaultValue'] = $argument['default'];
          }

          $arguments[$name] = new InputField($config);
        }
      }

      return $arguments;
    }

    return [];
  }

  /**
   * Build the argument type.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   * @param array|string $argument
   *   The argument definition array or type name.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildArgumentType(GraphQLSchemaManagerInterface $schemaManager, $argument) {
    if (is_array($argument) && array_key_exists('data_type', $argument) && $argument['data_type']) {
      $types = $schemaManager->find(function ($definition) use ($argument) {
        return array_key_exists('data_type', $definition) && $definition['data_type'] === $argument['data_type'];
      }, [
        GRAPHQL_CORE_TYPE_PLUGIN,
        GRAPHQL_CORE_INTERFACE_PLUGIN,
        GRAPHQL_CORE_SCALAR_PLUGIN,
      ]);

      $type = array_pop($types) ?: $schemaManager->findByName('String', [GRAPHQL_CORE_SCALAR_PLUGIN]);
    }
    else {
      $typeInfo = is_array($argument) ? $argument['type'] : $argument;

      $type = is_array($typeInfo) ? $this->buildEnumConfig($typeInfo) : $schemaManager->findByName($typeInfo, [
        GRAPHQL_CORE_INPUT_TYPE_PLUGIN,
        GRAPHQL_CORE_SCALAR_PLUGIN,
        GRAPHQL_CORE_ENUM_PLUGIN,
      ]);
    }

    if (isset($type) && $type instanceof TypeInterface) {
      $nullable = is_array($argument) && array_key_exists('nullable', $argument) && $argument['nullable'];
      $multi = is_array($argument) && array_key_exists('multi', $argument) && $argument['multi'];

      return $this->decorateType($type, $nullable, $multi);
    }

    return NULL;
  }

}
