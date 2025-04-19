<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the entity's uuid.
 *
 * @DataProducer(
 *   id = "entity_uuid",
 *   name = @Translation("Entity uuid"),
 *   description = @Translation("Returns the entity's uuid."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Uuid")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
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
