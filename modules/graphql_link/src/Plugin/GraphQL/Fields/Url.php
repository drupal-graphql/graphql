<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a link fields route object.
 *
 * @GraphQLField(
 *   id = "link_item_url",
 *   secure = true,
 *   name = "url",
 *   type = "Url",
 *   parents = {"LinkItem"}
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
