<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use DateTime;

/**
 * TODO: Should we derive this for each entity type individually?
 *
 * @GraphQLField(
 *   id = "entity_changed",
 *   secure = true,
 *   name = "entityChanged",
 *   type = "String",
 *   parents = {"Entity"},
 *   arguments = {
 *     "format" = "String"
 *   }
 * )
 */
class EntityChanged extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof EntityChangedInterface) {
      $datetime = new DateTime();
      $datetime->setTimestamp($value->getChangedTime());
      $format = isset($args['format']) ? $args['format'] : DateTime::ISO8601;
      yield $datetime->format($format);
    }
  }

}
