<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for Drupal routes.
 *
 * @GraphQLType(
 *   id = "url",
 *   name = "Url",
 *   fields = {"path", "alias", "routed"}
 * )
 */
class Url extends TypePluginBase {

}
