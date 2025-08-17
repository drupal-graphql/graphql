<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the entity type name of an entity.
 */
#[DataProducer(
  id: "entity_type_id",
  name: new TranslatableMarkup("Entity type"),
  description: new TranslatableMarkup("Returns an entity's entity type."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Entity type identifier"),
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
  ],
)]
class EntityType extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): string {
    return $entity->getEntityTypeId();
  }

}
