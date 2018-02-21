<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use DateTime;

/**
 * TODO: Should we derive this for each entity type individually?
 *
 * @GraphQLField(
 *   id = "entity_created",
 *   secure = true,
 *   name = "entityCreated",
 *   type = "String",
 *   parents = {"Entity"},
 *   arguments = {
 *     "format" = "String"
 *   }
 * )
 */
class EntityCreated extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    // `getCreatedTime` is on NodeInterface which feels weird, since there
    // is a generic `EntityInterface`. Checking for method existence for now.
    if (method_exists($value, 'getCreatedTime')) {
      $datetime = new DateTime();
      $datetime->setTimestamp($value->getCreatedTime());
      $format = isset($args['format']) ? $args['format'] : DateTime::ISO8601;
      yield $datetime->format($format);
    }
  }

}
