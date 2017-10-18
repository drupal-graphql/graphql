<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Views;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * Expose views as root fields.
 *
 * @GraphQLType(
 *   id = "view_result_type",
 *   provider = "views",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\ViewResultTypeDeriver"
 * )
 */
class ViewResultType extends TypePluginBase {

}
