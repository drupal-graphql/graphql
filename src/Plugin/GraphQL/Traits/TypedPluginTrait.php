<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\TypeInterface;

trait TypedPluginTrait {

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
      return $this->parseType($definition['type'], function ($type) use ($schemaBuilder) {
        return $schemaBuilder->findByDataTypeOrName($type, [
          GRAPHQL_SCALAR_PLUGIN,
          GRAPHQL_UNION_TYPE_PLUGIN,
          GRAPHQL_TYPE_PLUGIN,
          GRAPHQL_INTERFACE_PLUGIN,
          GRAPHQL_ENUM_PLUGIN,
        ])->getDefinition($schemaBuilder);
      });
    }

    return NULL;
  }

  /**
   * Parses a type definition from a string and properly decorates it.
   *
   * Converts type strings (e.g. [Foo!]) to their object representations.
   *
   * @param string $type
   *   The type string to parse.
   * @param callable $callback
   *   A callback for retrieving the concrete type object from the extracted
   *   type name.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The extracted type with the type decorators applied.
   */
  protected function parseType($type, callable $callback) {
    $decorators = [];
    $unwrapped = $type;
    $matches = NULL;

    while (preg_match('/[\[\]!]/', $unwrapped) && preg_match_all('/^(\[?)(.*?)(\]?)(!*?)$/', $unwrapped, $matches)) {
      if ($unwrapped === $matches[2][0] || empty($matches[1][0]) !== empty($matches[3][0])) {
        throw new \InvalidArgumentException(sprintf("Invalid type declaration '%s'.", $type));
      }

      if (!empty($matches[4][0])) {
        array_unshift($decorators, [$this, 'nonNullType']);
      }

      if (!empty($matches[1][0]) && !empty($matches[3][0])) {
        array_unshift($decorators, [$this, 'listType']);
      }

      $unwrapped = $matches[2][0];
    }

    return array_reduce($decorators, function ($carry, $current) {
      return $current($carry);
    }, $callback($unwrapped));
  }

  /**
   * Declares a type as non-nullable.
   *
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to wrap.
   *
   * @return \Youshido\GraphQL\Type\NonNullType
   *   The wrapped type.
   */
  protected function nonNullType(TypeInterface $type) {
    return new NonNullType($type);
  }

  /**
   * Declares a type as a list.
   *
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to wrap.
   *
   * @return \Youshido\GraphQL\Type\ListType\ListType
   *   The wrapped type.
   */
  protected function listType(TypeInterface $type) {
    return new ListType($type);
  }

}
