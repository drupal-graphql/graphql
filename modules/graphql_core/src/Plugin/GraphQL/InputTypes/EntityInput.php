<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_input",
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\EntityInputDeriver"
 * )
 */
class EntityInput extends InputTypePluginBase {

}
