<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Views;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * Expose types for fieldable views' rows.
 *
 * @GraphQLType(
 *   id = "view_row_type",
 *   provider = "views",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\ViewRowTypeDeriver"
 * )
 */
class ViewRowType extends TypePluginBase {

}
