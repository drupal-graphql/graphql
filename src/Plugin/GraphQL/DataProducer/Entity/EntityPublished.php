<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns whether the entity is published.
 *
 * @DataProducer(
 *   id = "entity_published",
 *   name = @Translation("Entity published"),
 *   description = @Translation("Returns whether the entity is published."),
 *   produces = @ContextDefinition("boolean",
 *     label = @Translation("Published"),
 *     required = FALSE
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityPublished extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool|null
   */
  public function resolve(EntityInterface $entity) {
    if ($entity instanceof EntityPublishedInterface) {
      return $entity->isPublished();
    }

    return NULL;
  }

}
