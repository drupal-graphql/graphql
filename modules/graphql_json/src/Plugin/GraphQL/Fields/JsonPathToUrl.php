<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\Component\Utility\NestedArray;
use Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\Route;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Extract Url objects from json paths.
 *
 * @GraphQLField(
 *   id = "json_path_url",
 *   name = "pathToUrl",
 *   secure = true,
 *   type = "Url",
 *   types = {"JsonObject", "JsonList"},
 *   arguments={
 *     "steps" = {
 *       "type" = "String",
 *       "multi" = true
 *     }
 *   }
 * )
 */
class JsonPathToUrl extends Route {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues(NULL, ['path' => NestedArray::getValue($value, $args['steps'])], $info) as $item) {
      yield $item;
    }
  }

}