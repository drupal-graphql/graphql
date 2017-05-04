<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a link fields route object.
 *
 * @GraphQLField(
 *   id = "link_item_url",
 *   name = "url",
 *   type = "Url",
 *   types = {"LinkItem"}
 * )
 */
class Url extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      yield $value->getUrl();
    }
  }

}
