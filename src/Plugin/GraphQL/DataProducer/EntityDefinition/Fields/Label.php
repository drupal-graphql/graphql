<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "label" from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_label',
  name: new TranslatableMarkup('Entity definition field label'),
  description: new TranslatableMarkup('Return entity definition field label.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition field label'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
class Label extends DataProducerPluginBase {

  /**
   * Resolves the field label.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string
   *   The field label.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): string {
    // Convert to string as label can be also TranslatableMarkup object.
    return (string) $entity_definition_field->getLabel();
  }

}
