<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the entity type name of an entity.
 *
 * @DataProducer(
 *   id = "entity_type_id",
 *   name = @Translation("Entity type"),
 *   description = @Translation("Returns an entity's entity type."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity type identifier")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityType extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): string {
    return $entity->getEntityTypeId();
  }

}
