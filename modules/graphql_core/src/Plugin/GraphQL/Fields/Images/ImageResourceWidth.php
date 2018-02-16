<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Images;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image width.
 *
 * @GraphQLField(
 *   id = "image_style_width",
 *   secure = true,
 *   name = "width",
 *   type = "Int",
 *   parents = {"ImageResource"},
 *   provider = "image"
 * )
 */
class ImageResourceWidth extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield (int) $value['width'];
  }

}
