<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "reference" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_reference',
  name: new TranslatableMarkup('Entity definition field reference'),
  description: new TranslatableMarkup('Return entity definition field reference.'),
  produces: new ContextDefinition(
    data_type: 'boolean',
    label: new TranslatableMarkup('Entity definition field reference'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
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
    return $entity_definition_field->getType() === 'entity_reference';
  }

}
