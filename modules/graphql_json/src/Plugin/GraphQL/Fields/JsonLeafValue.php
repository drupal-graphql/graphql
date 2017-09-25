<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve json leaf values.
 *
 * TODO: Right now this always returns strings. When Scalar union types are
 *       possible in GraphQL we could return the proper type instead.
 *
 * @GraphQLField(
 *   id = "json_leaf_value",
 *   secure = true,
 *   name = "value",
 *   type = "String",
 *   types = {"JsonLeaf"}
 * )
 */
class JsonLeafValue extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield (string) $value;
  }

}