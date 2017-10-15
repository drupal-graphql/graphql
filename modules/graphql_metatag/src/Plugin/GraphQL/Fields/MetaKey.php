<?php

namespace Drupal\graphql_metatag\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The metatags key field.
 *
 * @GraphQLField(
 *   id = "meta_key",
 *   name = "key",
 *   type = "String",
 *   types = {"MetaTag"},
 *   nullable = TRUE
 * )
 */
class MetaKey extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (is_array($value) && array_key_exists('key', $value)) {
      yield $value['key'];
    }
  }

}
