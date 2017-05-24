<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_query_filter_input",
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\EntityQueryFilterInputDeriver"
 * )
 */
class EntityQueryFilterInput extends InputTypePluginBase {

}
