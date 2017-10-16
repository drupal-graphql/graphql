<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_query_filter_input",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\InputTypes\EntityQueryFilterInputDeriver"
 * )
 */
class EntityQueryFilterInput extends InputTypePluginBase {

}
