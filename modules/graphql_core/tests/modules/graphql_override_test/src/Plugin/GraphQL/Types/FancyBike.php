<?php

namespace Drupal\graphql_override_test\Plugin\GraphQL\Types;

use Drupal\graphql_plugin_test\Plugin\GraphQL\Types\Bike;

/**
 * Replace our bike with an over-engineered one.
 *
 * Adds an inline gadget field. Because every bike needs gadgets.
 *
 * @GraphQLType(
 *   id = "fancy_bike",
 *   name = "Bike",
 *   interfaces = {"Vehicle"},
 *   weight = 1
 * )
 */
class FancyBike extends Bike {

}
