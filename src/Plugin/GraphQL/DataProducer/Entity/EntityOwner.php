<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Returns the user that owns the entity.
 */
#[DataProducer(
  id: "entity_owner",
  name: new TranslatableMarkup("Entity owner"),
  description: new TranslatableMarkup("Returns the entity owner."),
  produces: new EntityContextDefinition(
    data_type: "entity:user",
    label: new TranslatableMarkup("Owner"),
    required: FALSE
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ]
)]
class EntityOwner extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): ?UserInterface {
    if ($entity instanceof EntityOwnerInterface) {
      return $entity->getOwner();
    }

    return NULL;
  }

}
