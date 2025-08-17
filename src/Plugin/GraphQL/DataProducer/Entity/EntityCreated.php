<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the created time of an entity if it supports it.
 */
#[DataProducer(
  id: "entity_created",
  name: new TranslatableMarkup("Entity created date"),
  description: new TranslatableMarkup("Returns the entity created date."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Creation date"),
    required: FALSE
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
    "format" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Date format"),
      required: FALSE
    ),
  ]
)]
class EntityCreated extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @return string|null
   *   The formatted entity creation timestamp, or NULL if the entity does not
   *   support creation time.
   */
  public function resolve(EntityInterface $entity, ?string $format = NULL): ?string {
    // `getCreatedTime` is on NodeInterface which feels weird, since there
    // is a generic `EntityInterface`. Checking for method existence for now.
    if (method_exists($entity, 'getCreatedTime')) {
      $datetime = new \DateTime();
      $datetime->setTimestamp((int) $entity->getCreatedTime());
      return $datetime->format($format ?? \DateTime::ISO8601);
    }

    return NULL;
  }

}
