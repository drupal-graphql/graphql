<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve json list items.
 *
 * @GraphQLField(
 *   id = "json_list_items",
 *   secure = true,
 *   name = "items",
 *   type = "JsonNode",
 *   multi = true,
 *   types = {"JsonList"}
 * )
 */
class JsonListItems extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach ($value as $item) {
      yield $item;
    }
  }

}