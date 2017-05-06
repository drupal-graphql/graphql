<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image file type.
 *
 * @GraphQLField(
 *   id = "image_style_extension",
 *   name = "extension",
 *   type = "String",
 *   nullable = true,
 *   types = {"ImageStyle"}
 * )
 */
class ImageStyleExtension extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (array_key_exists('extension', $value)) {
      yield $value['extension'];
    }
  }

}
