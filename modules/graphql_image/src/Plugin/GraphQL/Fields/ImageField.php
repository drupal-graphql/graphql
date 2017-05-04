<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field override for image field.
 *
 * @GraphQLField(
 *   id = "image_field",
 *   field_formatter = "image",
 *   type = "Image",
 *   cache_tags = {"entity_field_info"},
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class ImageField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $image) {
        yield $image;
      }
    }
  }

}
