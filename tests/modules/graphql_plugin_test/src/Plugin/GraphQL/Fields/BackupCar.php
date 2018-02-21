<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $value['backupCar'];
  }

}
