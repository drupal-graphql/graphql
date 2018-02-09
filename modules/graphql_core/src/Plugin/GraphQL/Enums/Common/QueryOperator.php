<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Common;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "query_operator",
 *   name = "QueryOperator",
 *   values = {
 *     "=" = "EQUAL",
 *     "<>" = "NOT_EQUAL",
 *     "<" = "SMALLER_THAN",
 *     "<=" = "SMALLER_THAN_OR_EQUAL",
 *     ">" = "GREATER_THAN",
 *     ">=" = "GREATER_THAN_OR_EQUAL",
 *     "IN" = "IN",
 *     "NOT IN" = "NOT_IN",
 *     "LIKE" = "LIKE",
 *     "NOT LIKE" = "NOT_LIKE",
 *     "BETWEEN" = "BETWEEN",
 *     "NOT BETWEEN" = "NOT_BETWEEN",
 *     "IS NULL" = "IS_NULL",
 *     "IS NOT NULL" = "IS_NOT_NULL"
 *   }
 * )
 */
class QueryOperator extends EnumPluginBase {

}
