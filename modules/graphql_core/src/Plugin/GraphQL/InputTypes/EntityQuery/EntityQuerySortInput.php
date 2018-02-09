<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * @GraphQLInputType(
 *   id = "entity_query_sort_input",
 *   name = "EntityQuerySortInput",
 *   fields = {
 *     "field" = "String!",
 *     "direction" = {
 *       "type" = "SortEnum",
 *       "default" = "DESC"
 *     }
 *   }
 * )
 */
class EntityQuerySortInput extends InputTypePluginBase {

}
