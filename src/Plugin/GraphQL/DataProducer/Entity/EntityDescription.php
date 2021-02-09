<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the description text of an entity.
 *
 * @DataProducer(
 *   id = "entity_description",
 *   name = @Translation("Entity description"),
 *   description = @Translation("Returns the entity description."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Description")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityDescription extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string|null
   */
  public function resolve(EntityInterface $entity) {
    if ($entity instanceof EntityDescriptionInterface) {
      return $entity->getDescription();
    }

    return NULL;
  }

}
