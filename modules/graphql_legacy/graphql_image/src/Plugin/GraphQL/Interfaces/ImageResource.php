<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Interfaces;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * @GraphQLInterface(
 *   id = "image_resource",
 *   name = "ImageResource"
 * )
 */
class ImageResource extends InterfacePluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof ImageItem) {
      return $this->schemaManager->findByName('Image', [GRAPHQL_TYPE_PLUGIN]);
    }
    if (is_array($object)) {
      return $this->schemaManager->findByName('ImageDerivative', [GRAPHQL_TYPE_PLUGIN]);
    }
  }

}
