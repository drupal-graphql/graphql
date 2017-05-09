<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Interfaces;

use Drupal\graphql_core\GraphQL\InterfacePluginBase;

/**
 * ...
 *
 * @GraphQLInterface(
 *   id = "image_resource",
 *   name = "ImageResource",
 *   fields = {
 *     "image_url",
 *     "image_height",
 *     "image_width"
 *   }
 * )
 */
class ImageResource extends InterfacePluginBase {

  public function resolveType($object) {
    $debug = 1;
  }

}
