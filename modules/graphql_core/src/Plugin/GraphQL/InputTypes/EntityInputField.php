<?php

namespace Drupal\graphql_core\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity fields and their properties.
 *
 * @GraphQLInputType(
 *   id = "entity_input_field",
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\EntityInputFieldDeriver"
 * )
 */
class EntityInputField extends InputTypePluginBase {

}
