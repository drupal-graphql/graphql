<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "hidden" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_hidden',
  name: new TranslatableMarkup('Entity definition field hidden'),
  description: new TranslatableMarkup('Return entity definition field hidden.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition field hidden'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
    'entity_form_display_context' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity form display context'),
      required: FALSE,
    ),
  ],
)]
class Hidden extends DataProducerPluginBase {

  /**
   * Resolves the hidden property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   * @param \Drupal\Core\Entity\Entity\EntityFormDisplay|null $entity_form_display_context
   *   Entity form display context.
   *
   * @return bool
   *   If the field is hidden or not.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    ?EntityFormDisplay $entity_form_display_context,
  ): bool {
    if ($entity_form_display_context) {
      $hidden = $entity_form_display_context->get('hidden');
      $field_id = $entity_definition_field->getName();

      if (isset($hidden[$field_id])) {
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
