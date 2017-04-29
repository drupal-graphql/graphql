<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The obligatory echo service.
 *
 * @GraphQLField(
 *   name = "echo",
 *   type = "String",
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
