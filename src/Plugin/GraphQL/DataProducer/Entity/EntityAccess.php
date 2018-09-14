<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "entity_access",
 *   name = @Translation("Entity access"),
 *   description = @Translation("Returns whether the given user has entity access."),
 *   produces = @ContextDefinition("boolean",
 *     label = @Translation("Access result")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("Operation"),
 *       required = FALSE
 *     ),
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityAccess extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param null $operation
   * @param null $user
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   */
  public function resolve(EntityInterface $entity, $operation = NULL, $user = NULL) {
    return $entity->access($operation ?? 'view', $user);
  }

}
