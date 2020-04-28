<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "label" from a given entity definition.
 *
 * @DataProducer(
 *   id = "entity_definition_label",
 *   name = @Translation("Entity definition label"),
 *   description = @Translation("Return entity definition label."),
 *   consumes = {
 *     "entity_definition" = @ContextDefinition("any",
 *       label = @Translation("Entity definition")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition label")
 *   )
 * )
 */
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
