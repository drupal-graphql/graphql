<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image width.
 *
 * @GraphQLField(
 *   name = "url",
 *   type = "Url",
 *   nullable = true,
 *   types = {"ImageStyle"}
 * )
 */
class ImageStyleUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (array_key_exists('uri', $value)) {
      yield Url::fromUri(file_create_url($value['uri']));
    }
  }

}
