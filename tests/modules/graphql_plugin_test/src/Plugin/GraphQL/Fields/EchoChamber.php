<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The obligatory echo service.
 *
 * @GraphQLField(
 *   id = "echo",
 *   secure = true,
 *   name = "echo",
 *   type = "String",
 *   parents = {"Root"},
 *   arguments = {
 *     "input" = "String"
 *   }
 * )
 */
class EchoChamber extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $args['input'];
  }

}
