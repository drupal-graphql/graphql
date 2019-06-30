<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function resolve(EntityInterface $entity) {
    return $entity->label();
  }

}
