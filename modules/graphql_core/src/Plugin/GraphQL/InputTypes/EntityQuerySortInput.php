<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_query_sort_input",
 *   name = "EntityQuerySortInput",
 *   fields = {
 *     "field" = "String",
 *     "order" = {
 *        "type" = "SortOrder",
 *        "nullable" = true,
 *        "default" = "ASC"
 *      }
 *   }
 * )
 */
class EntityQuerySortInput extends InputTypePluginBase {

}
