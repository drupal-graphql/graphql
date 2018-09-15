<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Common;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "query_conjunction",
 *   name = "QueryConjunction",
 *   values = {
 *     "AND" = "AND",
 *     "OR" = "OR"
 *   }
 * )
 */
class QueryConjunction extends EnumPluginBase {

}
