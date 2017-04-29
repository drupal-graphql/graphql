<?php

namespace Drupal\graphql_core\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
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
   * Build the plugin type.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildType(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('type', $definition) && $definition['type']) {
        $type = $schemaManager->findByName($definition['type'], [
          GRAPHQL_CORE_SCALAR_PLUGIN,
          GRAPHQL_CORE_TYPE_PLUGIN,
          GRAPHQL_CORE_INTERFACE_PLUGIN,
        ]);
        if ($type instanceof TypeInterface) {
          return $this->decorateType($type, $definition['nullable'], $definition['multi']);
        }
      }
    }
    return NULL;
  }

}
