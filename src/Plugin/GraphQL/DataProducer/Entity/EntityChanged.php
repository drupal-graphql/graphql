<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the changed time of an entity if it supports it.
 *
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
   * Resolver.
   *
   * @throws \Exception
   *   When there are date handling errors.
   *
   * @return string|null
   *   The formatted entity changed timestamp, or NULL if the entity does not
   *   implement EntityChangedInterface.
   */
  public function resolve(EntityInterface $entity, ?string $format = NULL): ?string {
    if ($entity instanceof EntityChangedInterface) {
      $datetime = new \DateTime();
      $datetime->setTimestamp($entity->getChangedTime());
      return $datetime->format($format ?? \DateTime::ISO8601);
    }

    return NULL;
  }

}
