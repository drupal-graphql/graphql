<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "ID" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_id",
 *   name = @Translation("Entity definition field ID"),
 *   description = @Translation("Return entity definition field ID."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field ID")
 *   )
 * )
 */
class Id extends DataProducerPluginBase {

  /**
   * Resolves the ID property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string
   *   The field ID.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): string {
    return $entity_definition_field->getName();
  }

}
