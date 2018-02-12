<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Images;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image url.
 *
 * @GraphQLField(
 *   id = "image_style_url",
 *   secure = true,
 *   name = "url",
 *   type = "String",
 *   parents = {"ImageResource"},
 *   provider = "image"
 * )
 */
class ImageResourceUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['url'];
  }

}
