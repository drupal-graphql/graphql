<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A car in a car. Not sure what's so funny about that.
 *
 * @GraphQLField(
 *   id = "backup_car",
 *   secure = true,
 *   name = "backupCar",
 *   type = "Car",
 *   parents = {"Car"}
 * )
 */
class BackupCar extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['backupCar'];
  }

}
