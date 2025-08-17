<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "description" from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_description',
  name: new TranslatableMarkup('Entity definition field description'),
  description: new TranslatableMarkup('Return entity definition field description.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition field description'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
class Description extends DataProducerPluginBase {

  /**
   * Resolves the field description.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string|null
   *   The description.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): ?string {
    /** @var \Drupal\Component\Render\MarkupInterface|string|null $description */
    $description = $entity_definition_field->getDescription();
    // Convert translation object to string.
    if ($description instanceof MarkupInterface) {
      return (string) $description;
    }
    return $description;
  }

}
