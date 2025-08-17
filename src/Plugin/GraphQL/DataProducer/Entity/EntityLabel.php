<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the labels of an entity.
 */
#[DataProducer(
  id: "entity_label",
  name: new TranslatableMarkup("Entity label"),
  description: new TranslatableMarkup("Returns the entity label."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Label")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL
    ),
  ]
)]
class EntityLabel extends DataProducerPluginBase implements DataProducerPluginCachingInterface {

  /**
   * Resolver.
   *
   * @return string|null
   *   The entity label, or NULL if access to the entity label is denied for the
   *   given user.
   */
  public function resolve(EntityInterface $entity, ?AccountInterface $accessUser, FieldContext $context): ?string {
    /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
    $accessResult = $entity->access('view label', $accessUser, TRUE);
    $context->addCacheableDependency($accessResult);
    if ($accessResult->isAllowed()) {
      return $entity->label();
    }
    return NULL;
  }

}
