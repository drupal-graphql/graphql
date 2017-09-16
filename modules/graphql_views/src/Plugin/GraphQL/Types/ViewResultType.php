<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Expose views as root fields.
 *
 * @GraphQLType(
 *   id = "view_result_type",
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewResultTypeDeriver"
 * )
 */
class ViewResultType extends TypePluginBase {

}
