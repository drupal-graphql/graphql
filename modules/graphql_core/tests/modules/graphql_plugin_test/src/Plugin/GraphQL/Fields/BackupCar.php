<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A car in a car. Not sure what's so funny about that.
 *
 * @GraphQLField(
 *   id = "backup_car",
 *   name = "backupCar",
 *   type = "Car",
 *   types = {"Car"}
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
