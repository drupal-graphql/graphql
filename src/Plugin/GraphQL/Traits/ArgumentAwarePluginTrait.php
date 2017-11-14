<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
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
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\InputFieldInterface[]
   *   The list of arguments.
   */
  protected function buildArguments(PluggableSchemaBuilderInterface $schemaBuilder) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (!$definition['arguments']) {
        return [];
      }

      $arguments = [];
      foreach ($definition['arguments'] as $name => $argument) {
        $type = $this->buildArgumentType($schemaBuilder, $argument);

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
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   * @param array|string $argument
   *   The argument definition array or type name.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildArgumentType(PluggableSchemaBuilderInterface $schemaBuilder, $argument) {
    if (is_array($argument) && array_key_exists('data_type', $argument) && $argument['data_type']) {
      $plugin = $schemaBuilder->findByDataType($argument['data_type'], [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
      ]) ?: $schemaBuilder->findByName('String', [GRAPHQL_SCALAR_PLUGIN]);

      $type = $plugin->getDefinition($schemaBuilder);
    }
    else {
      $typeInfo = is_array($argument) ? $argument['type'] : $argument;

      $type = is_array($typeInfo) ? $this->buildEnumConfig($typeInfo, $argument['enum_type_name']) : $schemaBuilder->findByName($typeInfo, [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
        GRAPHQL_ENUM_PLUGIN,
      ])->getDefinition($schemaBuilder);
    }

    if (isset($type) && $type instanceof TypeInterface) {
      $nullable = is_array($argument) && (array_key_exists('nullable', $argument) && $argument['nullable'] || array_key_exists('default', $argument));
      $multi = is_array($argument) && array_key_exists('multi', $argument) && $argument['multi'];

      return $this->decorateType($type, $nullable, $multi);
    }

    return NULL;
  }

}
