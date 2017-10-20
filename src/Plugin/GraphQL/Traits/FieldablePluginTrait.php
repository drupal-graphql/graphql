<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Youshido\GraphQL\Field\FieldInterface;

/**
 * Methods for fieldable graphql plugins.
 */
trait FieldablePluginTrait {

  /**
   * Build the list of implicit and explicit fields attached to the object type.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\FieldInterface[]
   *   The type object.
   */
  protected function buildFields(SchemaBuilderInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();

      if ($definition['name']) {
        $types = array_merge([$definition['name']], array_key_exists('interfaces', $definition) ? $definition['interfaces'] : []);

        // Fields that are attached by annotating the type on the field.
        $implicitFields = $schemaManager->find(function ($field) use ($types) {
          return array_intersect($types, $field['parents']);
        }, [GRAPHQL_FIELD_PLUGIN]);

        // Implicit fields have higher precedence than explicit ones.
        // This makes fields overridable.
        return array_filter($implicitFields, function ($type) {
          return $type instanceof FieldInterface;
        });
      }
    }

    return [];
  }

}
