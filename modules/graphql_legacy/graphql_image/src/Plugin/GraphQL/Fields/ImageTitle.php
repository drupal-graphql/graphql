<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the image field title.
 *
 * @GraphQLField(
 *   id = "image_title",
 *   secure = true,
 *   name = "title",
 *   type = "String",
 *   nullable = true,
 *   parents = {"Image"}
 * )
 */
class ImageTitle extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      yield $value->title;
    }
  }

}
