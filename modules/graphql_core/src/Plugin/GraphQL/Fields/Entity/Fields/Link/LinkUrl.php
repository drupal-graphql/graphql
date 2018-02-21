<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\Fields\Link;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve a link fields route object.
 *
 * @GraphQLField(
 *   id = "link_item_url",
 *   secure = true,
 *   name = "url",
 *   type = "Url",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldPropertyDeriver",
 *   field_types = {"link"}
 * )
 */
class LinkUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      yield $value->getUrl();
    }
  }

}
