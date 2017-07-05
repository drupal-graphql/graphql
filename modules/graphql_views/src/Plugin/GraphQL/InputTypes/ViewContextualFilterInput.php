<?php

namespace Drupal\graphql_views\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Input types for view contextual filters.
 *
 * @GraphQLInputType(
 *   id = "view_contextual_filter_input",
 *   deriver = "\Drupal\graphql_views\Plugin\Deriver\ViewContextualFilterInputDeriver"
 * )
 */
class ViewContextualFilterInput extends InputTypePluginBase {

}
