<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the description text of an entity.
 */
#[DataProducer(
  id: "entity_description",
  name: new TranslatableMarkup("Entity description"),
  description: new TranslatableMarkup("Returns the entity description."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Description")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ]
)]
class EntityDescription extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): ?string {
    if ($entity instanceof EntityDescriptionInterface) {
      return $entity->getDescription();
    }

    return NULL;
  }

}
