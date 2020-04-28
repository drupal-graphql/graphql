<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "multiple" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_multiple",
 *   name = @Translation("Entity definition field multiple"),
 *   description = @Translation("Return entity definition field multiple."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field multiple")
 *   )
 * )
 */
class Multiple extends DataProducerPluginBase {

  /**
   * Resolves the "multiple" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return bool
   *   If the field contains multiple values or just single value.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): bool {
    return $entity_definition_field->isList();
  }

}
