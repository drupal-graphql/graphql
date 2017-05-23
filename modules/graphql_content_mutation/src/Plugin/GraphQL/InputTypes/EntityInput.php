<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "entity_input",
 *   deriver = "\Drupal\graphql_content_mutation\Plugin\Deriver\EntityInputDeriver"
 * )
 */
class EntityInput extends InputTypePluginBase {

}
