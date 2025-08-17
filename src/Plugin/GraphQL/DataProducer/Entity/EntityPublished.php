<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns whether the entity is published.
 */
#[DataProducer(
  id: "entity_published",
  name: new TranslatableMarkup("Entity published"),
  description: new TranslatableMarkup("Returns whether the entity is published."),
  produces: new ContextDefinition(
    data_type: "boolean",
    label: new TranslatableMarkup("Published"),
    required: FALSE,
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
  ],
)]
class EntityPublished extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): ?bool {
    if ($entity instanceof EntityPublishedInterface) {
      return $entity->isPublished();
    }

    return NULL;
  }

}
