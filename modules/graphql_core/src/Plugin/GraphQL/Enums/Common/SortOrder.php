<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Common;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "sort_order",
 *   name = "SortOrder",
 *   values = {
 *     "ASC" = "ASC",
 *     "DESC" = "DESC"
 *   }
 * )
 */
class SortOrder extends EnumPluginBase {

}
