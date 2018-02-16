<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Images;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image height.
 *
 * @GraphQLField(
 *   id = "image_style_height",
 *   secure = true,
 *   name = "height",
 *   type = "Int",
 *   parents = {"ImageResource"},
 *   provider = "image"
 * )
 */
class ImageResourceHeight extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield (int) $value['height'];
  }

}
