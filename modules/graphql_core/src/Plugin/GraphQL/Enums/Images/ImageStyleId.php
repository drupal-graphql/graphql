<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Images;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilder;
use Drupal\image\Entity\ImageStyle as ImageStyleConfig;

/**
 * @GraphQLEnum(
 *   id = "image_style_id",
 *   name = "ImageStyleId",
 *   provider = "image"
 * )
 */
class ImageStyleId extends EnumPluginBase {

  public function buildValues(SchemaBuilder $schemaManager) {
    $items = [];
    foreach (ImageStyleConfig::loadMultiple() as $imageStyle) {
      $items[$imageStyle->id()] = [
        'value' => $imageStyle->id(),
        'name' => $imageStyle->id(),
        'description' => $imageStyle->label()
      ];
    }
    return $items;
  }

}
