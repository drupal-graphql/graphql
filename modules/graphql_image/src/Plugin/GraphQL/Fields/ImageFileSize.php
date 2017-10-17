<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image file size.
 *
 * @GraphQLField(
 *   id = "image_file_size",
 *   secure = true,
 *   name = "fileSize",
 *   type = "Int",
 *   nullable = true,
 *   parents = {"Image"}
 * )
 */
class ImageFileSize extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield (int) $value->entity->getSize();
    }
    if (is_array($value) && array_key_exists('fileSize', $value)) {
      yield $value['fileSize'];
    }
  }

}
