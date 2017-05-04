<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * A bike type.
 *
 * @GraphQLType(
 *   id = "bike",
 *   name = "Bike",
 *   interfaces = {"Vehicle"}
 * )
 */
class Bike extends TypePluginBase {

}
