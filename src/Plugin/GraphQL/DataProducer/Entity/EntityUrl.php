<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL of an entity.
 *
 * @DataProducer(
 *   id = "entity_url",
 *   name = @Translation("Entity url"),
 *   description = @Translation("Returns the entity's url."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Url")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "absolute" = @ContextDefinition("boolean",
 *       label = @Translation("Make absolute"),
 *       required = FALSE,
 *       default_value = FALSE
 *     )
 *   }
 * )
 */
class EntityUrl extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get URL.
   * @param bool|null $absolute
   *   Make the URL absolute.
   *
   * @return \Drupal\Core\Url
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function resolve(EntityInterface $entity, bool $absolute = NULL) {
    return $entity->toUrl('canonical', ['absolute' => $absolute ?? FALSE]);
  }

}
