<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * A bike type.
 *
 * @GraphQLType(
 *   name = "Car",
 *   interfaces = { "Vehicle" },
 * )
 */
class Car extends TypePluginBase {

}
