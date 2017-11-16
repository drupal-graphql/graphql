<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A bike type.
 *
 * @GraphQLType(
 *   id = "car",
 *   name = "Car",
 *   interfaces = {"MotorizedVehicle"},
 * )
 */
class Car extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value, ResolveInfo $info = NULL) {
    return $value['type'] == 'Car';
  }

}
