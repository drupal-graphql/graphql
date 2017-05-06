<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Enums;

use Drupal\graphql_core\GraphQL\EnumPluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Drupal\image\Entity\ImageStyle as ImageStyleConfig;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;

/**
 * @GraphQLEnum(
 *   id = "image_style_id",
 *   name = "ImageStyleId"
 * )
 */
class ImageStyleId extends EnumPluginBase {

  public function buildValues(GraphQLSchemaManagerInterface $schemaManager) {
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
