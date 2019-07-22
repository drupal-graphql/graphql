<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "entity_changed",
 *   name = @Translation("Entity changed date"),
 *   description = @Translation("Returns the entity changed date."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Changed date"),
 *     required = FALSE
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "format" = @ContextDefinition("string",
 *       label = @Translation("Date format"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityChanged extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string|null $format
   *
   * @return string
   * @throws \Exception
   */
  public function resolve(EntityInterface $entity, $format = NULL) {
    if ($entity instanceof EntityChangedInterface) {
      $datetime = new \DateTime();
      $datetime->setTimestamp($entity->getChangedTime());
      return $datetime->format($format ?? \DateTime::ISO8601);
    }

    return NULL;
  }

}
