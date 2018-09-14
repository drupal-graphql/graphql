<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
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
