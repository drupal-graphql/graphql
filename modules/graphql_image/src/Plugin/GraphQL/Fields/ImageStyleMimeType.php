<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image file size.
 *
 * @GraphQLField(
 *   id = "image_style_mime_type",
 *   name = "mimeType",
 *   type = "String",
 *   nullable = true,
 *   types = {"ImageStyle"}
 * )
 */
class ImageStyleMimeType extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (array_key_exists('mimeType', $value)) {
      yield $value['mimeType'];
    }
  }

}
