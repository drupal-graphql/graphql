<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the bundle name of the entity.
 */
#[DataProducer(
  id: "entity_bundle",
  name: new TranslatableMarkup("Entity bundle"),
  description: new TranslatableMarkup("Returns the name of the entity's bundle."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Bundle")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ]
)]
class EntityBundle extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): string {
    return $entity->bundle();
  }

}
