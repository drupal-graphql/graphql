<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\EntityOwnerInterface;

/**
 * @DataProducer(
 *   id = "entity_owner",
 *   name = @Translation("Entity owner"),
 *   description = @Translation("Returns the entity owner."),
 *   produces = @ContextDefinition("entity:user",
 *     label = @Translation("Owner"),
 *     required = FALSE
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityOwner extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\user\UserInterface|null
   */
  public function resolve(EntityInterface $entity) {
    if ($entity instanceof EntityOwnerInterface) {
      return $entity->getOwner();
    }

    return NULL;
  }

}
