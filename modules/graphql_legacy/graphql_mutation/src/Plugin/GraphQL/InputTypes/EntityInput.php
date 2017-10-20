<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\InputTypes;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_input",
 *   deriver = "Drupal\graphql_mutation\Plugin\Deriver\InputTypes\EntityInputDeriver"
 * )
 */
class EntityInput extends InputTypePluginBase {

}
