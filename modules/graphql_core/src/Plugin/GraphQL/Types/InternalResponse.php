<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\graphql_core\Annotation\GraphQLType;
use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for internal http responses.
 *
 * @GraphQLType(
 *   id = "internal_response",
 *   name = "InternalResponse"
 * )
 */
class InternalResponse extends TypePluginBase {

}