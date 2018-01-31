<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Field\InputField;

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
    $arguments = [];

    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();

      foreach ($definition['arguments'] as $name => $argument) {
        $type = $this->buildArgumentType($schemaBuilder, $argument);
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
    $type = is_array($argument) ? $argument['type'] : $argument;
    return $this->parseType($type, function ($type) use ($schemaBuilder) {
      return $schemaBuilder->findByDataTypeOrName($type, [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
        GRAPHQL_ENUM_PLUGIN,
      ])->getDefinition($schemaBuilder);
    });
  }

}
