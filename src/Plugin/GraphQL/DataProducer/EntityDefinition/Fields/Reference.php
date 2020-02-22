<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "reference" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_reference",
 *   name = @Translation("Entity definition field reference"),
 *   description = @Translation("Return entity definition field reference."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field reference")
 *   )
 * )
 */
class Reference extends DataProducerPluginBase {

  /**
   * Resolves the "reference" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return bool
   *   If the field is referencing entities (is the entity reference type).
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): bool {
    if ($entity_definition_field instanceof BaseFieldDefinition) {
      /** @var \Drupal\Core\Field\BaseFieldDefinition $entity_definition_field */
      if ($entity_definition_field->getType() === 'entity_reference') {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    elseif ($entity_definition_field instanceof FieldConfig) {
      /** @var \Drupal\field\Entity\FieldConfig $entity_definition_field */
      if ($entity_definition_field->getType() === 'entity_reference') {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    elseif ($entity_definition_field instanceof BaseFieldOverride) {
      /** @var \Drupal\field\Entity\FieldConfig $entity_definition_field */
      if ($entity_definition_field->getType() === 'entity_reference') {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return FALSE;
    }
  }

}
