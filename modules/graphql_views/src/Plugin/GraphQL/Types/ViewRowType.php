<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

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
