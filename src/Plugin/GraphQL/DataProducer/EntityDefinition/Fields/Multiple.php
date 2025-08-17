<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "multiple" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_multiple',
  name: new TranslatableMarkup('Entity definition field multiple'),
  description: new TranslatableMarkup('Return entity definition field multiple.'),
  produces: new ContextDefinition(
    data_type: 'boolean',
    label: new TranslatableMarkup('Entity definition field multiple'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
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
    if ($entity_definition_field instanceof FieldStorageDefinitionInterface) {
      return $entity_definition_field->isMultiple();
    }
    return $entity_definition_field->getFieldStorageDefinition()->isMultiple();
  }

}
