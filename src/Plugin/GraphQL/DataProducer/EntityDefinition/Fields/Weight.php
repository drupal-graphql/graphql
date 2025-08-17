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
 * Retrieves the weight value of a field.
 */
#[DataProducer(
  id: 'entity_definition_field_weight',
  name: new TranslatableMarkup('Entity definition field weight'),
  description: new TranslatableMarkup('Return entity definition field weight.'),
  produces: new ContextDefinition(
    data_type: 'integer',
    label: new TranslatableMarkup('Entity definition field weight'),
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
class Weight extends DataProducerPluginBase {

  /**
   * Resolves the "weight" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   * @param \Drupal\Core\Entity\Entity\EntityFormDisplay|null $entity_form_display_context
   *   Entity form display context.
   *
   * @return int
   *   The field weight.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    ?EntityFormDisplay $entity_form_display_context,
  ): int {
    if ($entity_form_display_context) {
      $content = $entity_form_display_context->get('content');
      $field_id = $entity_definition_field->getName();

      if (isset($content[$field_id])) {
        return (int) $content[$field_id]['weight'];
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
