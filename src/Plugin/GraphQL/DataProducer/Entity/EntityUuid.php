<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the entity's uuid.
 */
#[DataProducer(
  id: "entity_uuid",
  name: new TranslatableMarkup("Entity uuid"),
  description: new TranslatableMarkup("Returns the entity's uuid."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Uuid")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ],
)]
class EntityUuid extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @return string|null
   *   The entity UUID, or NULL if the entity doesn't have a UUID.
   */
  public function resolve(EntityInterface $entity): ?string {
    return $entity->uuid();
  }

}
