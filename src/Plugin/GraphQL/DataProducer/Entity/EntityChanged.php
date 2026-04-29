<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the changed time of an entity if it supports it.
 */
#[DataProducer(
  id: "entity_changed",
  name: new TranslatableMarkup("Entity changed date"),
  description: new TranslatableMarkup("Returns the entity changed date."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Changed date"),
    required: FALSE,
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
    "format" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Date format"),
      required: FALSE,
    ),
  ],
)]
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
      $datetime->setTimestamp((int) $entity->getChangedTime());
      return $datetime->format($format ?? \DateTime::ISO8601);
    }

    return NULL;
  }

}
