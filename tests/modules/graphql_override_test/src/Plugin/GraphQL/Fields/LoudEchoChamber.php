<?php

namespace Drupal\graphql_override_test\Plugin\GraphQL\Fields;

use Drupal\graphql_plugin_test\Plugin\GraphQL\Fields\EchoChamber;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * This field will replace the existing "echo" field with a CAPSLOCK version.
 *
 * @GraphQLField(
 *   id = "loud_echo",
 *   secure = true,
 *   name = "echo",
 *   type = "String",
 *   arguments = {
 *     "input" = "String"
 *   },
 *   weight = 1
 * )
 */
class LoudEchoChamber extends EchoChamber {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues($value, $args, $info) as $echo) {
      /** @var string $echo */
      yield strtoupper($echo);
    }
  }

}
