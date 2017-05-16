<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image height.
 *
 * @GraphQLField(
 *   id = "image_style_height",
 *   name = "height",
 *   type = "Int",
 *   nullable = true,
 *   types = {"ImageResource"}
 * )
 */
class ImageResourceHeight extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield (int) $value->height;
    }
    if (is_array($value) && array_key_exists('height', $value)) {
      yield $value['height'];
    }
  }

}
