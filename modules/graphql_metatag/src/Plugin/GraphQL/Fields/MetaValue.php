<?php

namespace Drupal\graphql_metatag\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The metatags content field.
 *
 * @GraphQLField(
 *   id = "meta_value",
 *   name = "value",
 *   type = "String",
 *   types = {"MetaTag"}
 * )
 */
class MetaValue extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (is_array($value) && array_key_exists('value', $value)) {
      yield $value['value'];
    }
  }

}
