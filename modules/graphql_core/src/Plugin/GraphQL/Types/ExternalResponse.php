<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\graphql_core\Annotation\GraphQLType;
use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for external http responses.
 *
 * @GraphQLType(
 *   id = "external_response",
 *   name = "ExternalResponse"
 * )
 */
class ExternalResponse extends TypePluginBase {

}