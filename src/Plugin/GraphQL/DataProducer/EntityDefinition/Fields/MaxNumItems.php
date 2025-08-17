<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "max_num_items" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_max_num_items',
  name: new TranslatableMarkup('Entity definition field max_num_items'),
  description: new TranslatableMarkup('Return entity definition field max_num_items.'),
  produces: new ContextDefinition(
    data_type: 'integer',
    label: new TranslatableMarkup('Entity definition field max_num_items'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
class MaxNumItems extends DataProducerPluginBase {

  /**
   * Resolves the "max_num_items" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return int
   *   The maximum number of items for a field.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): int {
    if ($entity_definition_field instanceof BaseFieldDefinition) {
      return $entity_definition_field->getCardinality();
    }
    return $entity_definition_field->getFieldStorageDefinition()->getCardinality();
  }

}
