<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for non-Drupal urls.
 *
 * @GraphQLType(
 *   id = "external_url",
 *   name = "ExternalUrl",
 *   interfaces={"Url"}
 * )
 */
class ExternalUrl extends TypePluginBase {

}
