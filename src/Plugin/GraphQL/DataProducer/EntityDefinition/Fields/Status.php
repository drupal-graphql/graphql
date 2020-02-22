<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "status" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_status",
 *   name = @Translation("Entity definition field status"),
 *   description = @Translation("Return entity definition field status."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field status")
 *   )
 * )
 */
class Status extends DataProducerPluginBase {

  /**
   * Resolves the "status" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return bool
   *   If the field config is enabled or not.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): bool {
    if ($entity_definition_field instanceof BaseFieldDefinition) {
      /** @var \Drupal\Core\Field\BaseFieldDefinition $entity_definition_field */
      return TRUE;
    }
    elseif ($entity_definition_field instanceof FieldConfig) {
      /** @var \Drupal\field\Entity\FieldConfig $entity_definition_field */
      return $entity_definition_field->status();
    }
    elseif ($entity_definition_field instanceof BaseFieldOverride) {
      /** @var \Drupal\Core\Field\Entity\BaseFieldOverride $entity_definition_field */
      return $entity_definition_field->status();
    }

    return FALSE;
  }

}
