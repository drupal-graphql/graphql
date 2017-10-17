<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The number of wheels a vehicle has.
 *
 * @GraphQLField(
 *   id = "wheels",
 *   secure = true,
 *   name = "wheels",
 *   type = "Int",
 *   parents = {"Vehicle"}
 * )
 */
class Wheels extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['wheels'];
  }

}
