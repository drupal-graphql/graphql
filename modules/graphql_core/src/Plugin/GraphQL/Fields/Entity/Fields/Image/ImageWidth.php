<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\Fields\Image;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve the image width.
 *
 * @GraphQLField(
 *   id = "image_width",
 *   secure = true,
 *   name = "width",
 *   type = "Int",
 *   provider = "image",
 *   field_types = {"image"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldPropertyDeriver"
 * )
 */
class ImageWidth extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity && $value->entity->access('view')) {
      yield (int) $value->width;
    }
  }

}
