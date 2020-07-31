<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the weight value of a field.
 *
 * @DataProducer(
 *   id = "entity_definition_field_weight",
 *   name = @Translation("Entity definition field weight"),
 *   description = @Translation("Return entity definition field weight."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     ),
 *     "entity_form_display_context" = @ContextDefinition("any",
 *       label = @Translation("Entity form display context"),
 *       required = FALSE,
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field weight")
 *   )
 * )
 */
class Weight extends DataProducerPluginBase {

  /**
   * Resolves the "weight" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   * @param array|null $entity_form_display_context
   *   Entity form display context.
   *
   * @return int
   *   The field weight.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    ?array $entity_form_display_context = NULL
  ): int {
    if ($entity_form_display_context) {
      $entity_form_display = $entity_form_display_context['key'];
      $content = $entity_form_display->get('content');
      $field_id = $entity_definition_field->getName();

      if (isset($content[$field_id])) {
        return $content[$field_id]['weight'];
      }
      else {
        return 0;
      }
    }
    else {
      return 0;
    }
  }

}
