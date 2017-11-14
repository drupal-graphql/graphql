<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Enum\EnumType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Methods for GraphQL plugins that have a type.
 */
trait TypedPluginTrait {

  /**
   * Add information about cardinality and nullability.
   *
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The initial type object.
   * @param bool $nullable
   *   Indicates if the type can be null.
   * @param bool $multi
   *   Indicates if the type is a list.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The decorated type
   */
  public function decorateType(TypeInterface $type, $nullable, $multi) {
    if ($type) {
      if ($multi) {
        $type = new ListType($type);
      }
      if (!$nullable) {
        $type = new NonNullType($type);
      }
    }
    return $type;
  }

  /**
   * Turn a list of options into an EnumType.
   *
   * @param string[] $options
   *   A list of options.
   * @param string $typeName
   *   The name for the enum type.
   *
   * @return EnumType
   *   The enumeration type.
   */
  protected function buildEnumConfig(array $options, $typeName) {
    $values = [];
    foreach ($options as $value => $description) {
      $values[] = [
        'value' => $value,
        'name' => strtoupper($value),
        'description' => $description,
      ];
    }

    return new EnumType([
      'name' => $typeName,
      'values' => $values,
    ]);
  }

  /**
   * Build the plugin type.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildType(PluggableSchemaBuilderInterface $schemaBuilder) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();

      if (array_key_exists('data_type', $definition) && $definition['data_type']) {
        $type = $schemaBuilder->findByDataType($definition['data_type'])->getDefinition($schemaBuilder);
      }
      else if (array_key_exists('type', $definition) && $definition['type']) {
        $type = is_array($definition['type']) ? $this->buildEnumConfig($definition['type'], $definition['enum_type_name']) : $schemaBuilder->findByName($definition['type'], [
          GRAPHQL_SCALAR_PLUGIN,
          GRAPHQL_UNION_TYPE_PLUGIN,
          GRAPHQL_TYPE_PLUGIN,
          GRAPHQL_INTERFACE_PLUGIN,
          GRAPHQL_ENUM_PLUGIN,
        ])->getDefinition($schemaBuilder);
      }

      if (isset($type) && $type instanceof TypeInterface) {
        return $this->decorateType($type, $definition['nullable'], $definition['multi']);
      }
    }

    return NULL;
  }

}
