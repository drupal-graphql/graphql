<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity fields and their properties.
 *
 * @GraphQLInputType(
 *   id = "entity_input_field",
 *   deriver = "\Drupal\graphql_content_mutation\Plugin\Deriver\EntityInputFieldDeriver"
 * )
 */
class EntityInputField extends InputTypePluginBase {

}
