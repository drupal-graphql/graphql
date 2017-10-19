<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Images;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image height.
 *
 * @GraphQLField(
 *   id = "image_style_height",
 *   secure = true,
 *   name = "height",
 *   type = "Int",
 *   nullable = true,
 *   provider = "image",
 *   parents = {"ImageResource"},
 *   field_types = {"image"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldPropertyDeriver"
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
      yield (int) $value['height'];
    }
  }

}
