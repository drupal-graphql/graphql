<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Link;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Link;

/**
 * Retrieve a link's route object.
 *
 * @GraphQLField(
 *   id = "link_url",
 *   secure = true,
 *   name = "url",
 *   description = @Translation("The url of a link."),
 *   type = "Url",
 *   parents = {"Link"}
 * )
 */
class LinkUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Link) {
      yield $value->getUrl();
    }
  }

}
