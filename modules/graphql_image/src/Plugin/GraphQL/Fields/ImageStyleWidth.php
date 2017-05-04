<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image width.
 *
 * @GraphQLField(
 *   id = "image_style_width",
 *   name = "width",
 *   type = "Int",
 *   nullable = true,
 *   types = {"ImageStyle"}
 * )
 */
class ImageStyleWidth extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (array_key_exists('width', $value)) {
      yield $value['width'];
    }
  }

}
