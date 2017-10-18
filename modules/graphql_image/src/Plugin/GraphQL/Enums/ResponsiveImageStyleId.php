<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Enums;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilder;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;

/**
 * @GraphQLEnum(
 *   id = "responsive_image_style_id",
 *   name = "ResponsiveImageStyleId"
 * )
 */
class ResponsiveImageStyleId extends EnumPluginBase {

  public function buildValues(SchemaBuilder $schemaManager) {
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
