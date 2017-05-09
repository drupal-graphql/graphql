<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image field title.
 *
 * @GraphQLField(
 *   id = "image_original",
 *   name = "original",
 *   type = "ImageResource",
 *   nullable = true,
 *   types = {"Image"}
 * )
 */
class ImageOriginal extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $value->entity;
      yield [
        'url' => file_create_url($file->getFileUri()),
        'width' => (int) $value->width,
        'height' => (int) $value->height,
      ];
    }
  }

}
