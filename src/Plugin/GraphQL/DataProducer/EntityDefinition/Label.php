<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "label" from a given entity definition.
 */
#[DataProducer(
  id: 'entity_definition_label',
  name: new TranslatableMarkup('Entity definition label'),
  description: new TranslatableMarkup('Return entity definition label.'),
  produces: new ContextDefinition(
    data_type: 'string',
    label: new TranslatableMarkup('Entity definition label'),
  ),
  consumes: [
    'entity_definition' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition'),
    ),
  ],
)]
class Label extends DataProducerPluginBase {

  /**
   * Resolves the entity definition label.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_definition
   *   The entity type definition.
   *
   * @return string
   *   The entity definition label.
   */
  public function resolve(EntityTypeInterface $entity_definition): string {
    // Convert to string as label can be also TranslatableMarkup object.
    return (string) $entity_definition->getLabel();
  }

}
