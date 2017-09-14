<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Expose types for fieldable views' rows.
 *
 * @GraphQLType(
 *   id = "view_row_type",
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewRowTypeDeriver"
 * )
 */
class ViewRowType extends TypePluginBase {

}
