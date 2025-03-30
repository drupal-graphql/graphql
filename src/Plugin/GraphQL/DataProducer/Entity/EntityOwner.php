<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Returns the user that owns the entity.
 *
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
   * Resolver.
   */
  public function resolve(EntityInterface $entity): ?UserInterface {
    if ($entity instanceof EntityOwnerInterface) {
      return $entity->getOwner();
    }

    return NULL;
  }

}
