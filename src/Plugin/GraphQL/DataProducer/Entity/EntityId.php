<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the ID of an entity.
 */
#[DataProducer(
  id: "entity_id",
  name: new TranslatableMarkup("Entity identifier"),
  description: new TranslatableMarkup("Returns the entity identifier."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Identifier")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ],
)]
class EntityId extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @return int|string|null
   *   The entity ID as integer or string, null if the entity has no ID yet.
   */
  public function resolve(EntityInterface $entity): int|string|null {
    return $entity->id();
  }

}
