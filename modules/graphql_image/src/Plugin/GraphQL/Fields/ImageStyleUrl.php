<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image url.
 *
 * @GraphQLField(
 *   id = "image_style_url",
 *   name = "url",
 *   type = "String",
 *   nullable = true,
 *   types = {"ImageStyle"}
 * )
 */
class ImageStyleUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (array_key_exists('url', $value)) {
      yield $value['url'];
    }
  }

}
