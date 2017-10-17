<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Interfaces;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * Vehicle interface definition.
 *
 * @GraphQLInterface(
 *   id = "motorized_vehicle",
 *   name = "MotorizedVehicle",
 *   interfaces = {"Vehicle"}
 * )
 */
class MotorizedVehicle extends InterfacePluginBase {

}
