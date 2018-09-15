<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for entity query result sets.
 *
 * @GraphQLType(
 *   id = "entity_query_result",
 *   name = "EntityQueryResult",
 *   description = @Translation("Wrapper type for entity query results containing the list of loaded entities and the full entity count for pagination purposes.")
 * )
 */
class EntityQueryResult extends TypePluginBase {

}
