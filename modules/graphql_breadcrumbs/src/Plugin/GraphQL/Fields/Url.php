<?php

namespace Drupal\graphql_breadcrumbs\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Drupal\Core\Link;

/**
 * Retrieve a link's route object.
 *
 * @GraphQLField(
 *   id = "link_url",
 *   name = "url",
 *   type = "Url",
 *   types = {"Link"}
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
