<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return null|string
   */
  public function resolve(EntityInterface $entity) {
    return $entity->uuid();
  }

}
