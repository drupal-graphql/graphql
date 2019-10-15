<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "type" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_type",
 *   name = @Translation("Entity definition field type"),
 *   description = @Translation("Return entity definition field type."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field type")
 *   )
 * )
 */
class Type extends DataProducerPluginBase {

  /**
   * Resolves the "type" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string
   *   The field type.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): string {
    return $entity_definition_field->getType();
  }

}
