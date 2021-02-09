<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the labels of an entity.
 *
 * @DataProducer(
 *   id = "entity_label",
 *   name = @Translation("Entity label"),
 *   description = @Translation("Returns the entity label."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Label")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityLabel extends DataProducerPluginBase implements DataProducerPluginCachingInterface {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string|null
   */
  public function resolve(EntityInterface $entity) {
    return $entity->label();
  }

}
