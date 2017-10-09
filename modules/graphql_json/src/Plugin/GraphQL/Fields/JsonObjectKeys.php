<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the keys of a json object.
 *
 * @GraphQLField(
 *   id = "json_object_keys",
 *   secure = true,
 *   name = "keys",
 *   type = "String",
 *   multi = true,
 *   types = {"JsonObject"}
 * )
 */
class JsonObjectKeys extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (array_keys($value) as $key) {
      yield $key;
    }
  }

}