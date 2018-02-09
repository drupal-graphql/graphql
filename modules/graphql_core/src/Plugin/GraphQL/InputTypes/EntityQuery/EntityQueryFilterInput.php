<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * @GraphQLInputType(
 *   id = "entity_query_filter_input",
 *   name = "EntityQueryFilterInput",
 *   fields = {
 *     "conditions" = "[EntityQueryFilterConditionInput]",
 *     "groups" = "[EntityQueryFilterInput]",
 *     "conjunction" = {
 *       "type" = "QueryConjunction",
 *       "default" = "AND"
 *     }
 *   }
 * )
 */
class EntityQueryFilterInput extends InputTypePluginBase {

}
