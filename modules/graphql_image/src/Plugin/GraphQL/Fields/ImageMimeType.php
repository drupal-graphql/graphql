<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image file size.
 *
 * @GraphQLField(
 *   id = "image_mime_type",
 *   name = "mimeType",
 *   type = "String",
 *   nullable = true,
 *   types = {"Image"}
 * )
 */
class ImageMimeType extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield $value->entity->getMimeType();
    }
    if (is_array($value) && array_key_exists('mimeType', $value)) {
      yield $value['mimeType'];
    }
  }

}
