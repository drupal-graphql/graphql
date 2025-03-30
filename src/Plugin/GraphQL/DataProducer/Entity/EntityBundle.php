<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the bundle name of the entity.
 *
 * @DataProducer(
 *   id = "entity_bundle",
 *   name = @Translation("Entity bundle"),
 *   description = @Translation("Returns the name of the entity's bundle."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Bundle")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityBundle extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): string {
    return $entity->bundle();
  }

}
