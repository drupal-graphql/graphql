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
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns whether the given user has access to the entity.
 */
#[DataProducer(
  id: "entity_access",
  name: new TranslatableMarkup("Entity access"),
  description: new TranslatableMarkup("Returns whether the given user has entity access."),
  produces: new ContextDefinition(
    data_type: "boolean",
    label: new TranslatableMarkup("Access result")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
    "operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE
    ),
    "user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE
    ),
  ]
)]
class EntityAccess extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check access for.
   * @param string $operation
   *   The access operation, for example "view".
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user account access should be checked for.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The context to add caching information to.
   *
   * @return bool
   *   TRUE when access to the entity is allowed, FALSE otherwise.
   */
  public function resolve(EntityInterface $entity, ?string $operation, ?AccountInterface $user, FieldContext $context): bool {
    $result = $entity->access($operation ?? 'view', $user, TRUE);
    $context->addCacheableDependency($result);
    return $result->isAllowed();
  }

}
