<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image file size.
 *
 * @GraphQLField(
 *   id = "image_file_size",
 *   name = "fileSize",
 *   type = "Int",
 *   nullable = true,
 *   types = {"Image"}
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
