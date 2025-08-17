<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "ID" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_id',
  name: new TranslatableMarkup('Entity definition field ID'),
  description: new TranslatableMarkup('Return entity definition field ID.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition field ID'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
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
