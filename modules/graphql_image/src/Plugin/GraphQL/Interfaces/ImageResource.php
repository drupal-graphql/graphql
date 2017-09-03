<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Interfaces;

use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

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

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof ImageItem) {
      return $this->schemaManager->findByName('Image', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
    if (is_array($object)) {
      return $this->schemaManager->findByName('ImageDerivative', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
  }

}
