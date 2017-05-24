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
 *   types = {"ImageResource"}
 * )
 */
class ImageResourceWidth extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield (int) $value->width;
    }
    if (is_array($value) && array_key_exists('width', $value)) {
      yield $value['width'];
    }
  }

}
