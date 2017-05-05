<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * A test object with a cachable and a uncachable field.
 *
 * @GraphQLType(
 *   id = "cache_object",
 *   name = "Object"
 * )
 */
class Object extends TypePluginBase {

}
