<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Enums;

use Drupal\graphql_core\GraphQL\EnumPluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;

/**
 * @GraphQLEnum(
 *   id = "responsive_image_style_id",
 *   name = "ResponsiveImageStyleId"
 * )
 */
class ResponsiveImageStyleId extends EnumPluginBase {

  public function buildValues(GraphQLSchemaManagerInterface $schemaManager) {
    $items = [];
    foreach (ResponsiveImageStyle::loadMultiple() as $responsiveImageStyle) {
      $items[$responsiveImageStyle->id()] = [
        'value' => $responsiveImageStyle->id(),
        'name' => $responsiveImageStyle->id(),
        'description' => $responsiveImageStyle->label()
      ];
    }
    return $items;
  }

}
