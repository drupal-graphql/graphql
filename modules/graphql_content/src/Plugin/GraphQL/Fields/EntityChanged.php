<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use DateTime;

/**
 * Get the entities changed date if available.
 *
 * @GraphQLField(
 *   id = "entity_changed",
 *   name = "entityChanged",
 *   type = "String",
 *   types = {"Entity"}
 * )
 */
class EntityChanged extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityChangedInterface) {
      $datetime = new DateTime();
      $datetime->setTimestamp($value->getChangedTime());
      yield $datetime->format(DateTime::ISO8601);
    }
  }

}
