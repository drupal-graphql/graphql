<?php

namespace Drupal\graphql_views\Plugin\GraphQL\InputTypes;

use Drupal\graphql_core\GraphQL\InputTypePluginBase;

/**
 * Creates input types for entity mutations.
 *
 * @GraphQLInputType(
 *   id = "view_filter_input",
 *   deriver = "\Drupal\graphql_views\Plugin\Deriver\ViewFilterInputDeriver"
 * )
 */
class ViewFilterInput extends InputTypePluginBase {

}
