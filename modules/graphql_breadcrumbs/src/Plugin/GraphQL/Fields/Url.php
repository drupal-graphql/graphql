<?php

namespace Drupal\graphql_breadcrumbs\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use Drupal\Core\Link;

/**
 * Retrieve a link's route object.
 *
 * @GraphQLField(
 *   id = "link_url",
 *   secure = true,
 *   name = "url",
 *   type = "Url",
 *   parents = {"Link"}
 * )
 */
class Url extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Link) {
      yield $value->getUrl();
    }
  }

}
