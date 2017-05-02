<?php

namespace Drupal\graphql_core\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Field\FieldInterface;

/**
 * Methods for fieldable graphql plugins.
 */
trait FieldablePluginTrait {

  /**
   * Build the list of implicit and explicit fields attached to the object type.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\FieldInterface[]
   *   The type object.
   */
  protected function buildFields(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();

      $explicitFields = [];
      if ($definition['fields']) {
        // Fields that are annotated on the type itself.
        $explicitFields = $schemaManager->find(function ($field) use ($definition) {
          return in_array($field['name'], $definition['fields']);
        }, [GRAPHQL_CORE_FIELD_PLUGIN]);
      }

      $implicitFields = [];
      if ($definition['name']) {
        // Fields that are attached by annotating the type on the field.
        $implicitFields = $schemaManager->find(function ($field) use ($definition) {
          return in_array($definition['name'], $field['types']);
        }, [GRAPHQL_CORE_FIELD_PLUGIN]);
      }

      // Implicit fields have higher precedence than explicit ones.
      // This makes fields overridable.
      return array_filter($implicitFields + $explicitFields, function ($type) {
        return $type instanceof FieldInterface;
      });
    }
    return [];
  }

}
