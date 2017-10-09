<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * GraphQL Type for Drupal image fields.
 *
 * @GraphQLType(
 *   id = "image",
 *   name = "Image",
 *   interfaces = {"ImageResource"}
 * )
 */
class Image extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    return $value instanceof ImageItem;
  }

}
