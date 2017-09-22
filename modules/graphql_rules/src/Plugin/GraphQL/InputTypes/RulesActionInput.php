<?php

namespace Drupal\graphql_rules\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for rules actions.
 *
 * @GraphQLInputType(
 *   id = "rules_action_input",
 *   deriver = "\Drupal\graphql_rules\Plugin\Deriver\RulesActionInputDeriver"
 * )
 */
class EntityInput extends InputTypePluginBase {

}
