<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * A bike type.
 *
 * @GraphQLType(
 *   id = "car",
 *   name = "Car",
 *   interfaces = {"Vehicle"},
 * )
 */
class Car extends TypePluginBase {

}
