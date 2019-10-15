<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "required" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_required",
 *   name = @Translation("Entity definition field required"),
 *   description = @Translation("Return entity definition field required."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field required")
 *   )
 * )
 */
class Required extends DataProducerPluginBase {

  /**
   * Resolves the "required" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return bool
   *   If the field is required or not.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): bool {
    return $entity_definition_field->isRequired();
  }

}
