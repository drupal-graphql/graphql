<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image field title.
 *
 * @GraphQLField(
 *   id = "image_alt",
 *   name = "alt",
 *   type = "String",
 *   nullable = true,
 *   types = {"Image"}
 * )
 */
class ImageAlt extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield $value->alt;
    }
  }

}
