<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * Creates input types for entity fields and their properties.
 *
 * @GraphQLInputType(
 *   id = "entity_input_field",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\InputTypes\EntityInputFieldDeriver"
 * )
 */
class EntityInputField extends InputTypePluginBase {

}
