<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "default_value" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_default_value",
 *   name = @Translation("Entity definition field default_value"),
 *   description = @Translation("Return entity definition field default_value."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field default_value")
 *   )
 * )
 */
class DefaultValue extends DataProducerPluginBase {

  /**
   * Resolves the default value property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string|bool|int|null
   *   The default value.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field) {
    $default_value = $entity_definition_field->getDefaultValueLiteral();
    switch ($entity_definition_field->getType()) {
      case 'list_integer':
      case 'list_string':
      case 'text_long':
        return $default_value[0]['value'] ?? NULL;

      case 'boolean':
        return (bool) ($default_value[0]['value'] ?? FALSE);
    }
    return NULL;
  }

}
