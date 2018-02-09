<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Links;

use Drupal\Component\Utility\NestedArray;
use Drupal\link\LinkItemInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve specific attributes of a menu link.
 *
 * @GraphQLField(
 *   id = "link_item_attribute",
 *   secure = true,
 *   name = "attribute",
 *   type = "String",
 *   arguments = {
 *     "key" = "String!"
 *   },
 *   field_types = {"link"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldPropertyDeriver"
 * )
 */
class LinkAttribute extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      $options = $value->getUrl()->getOptions();
      yield NestedArray::getValue($options, ['attributes', $args['key']]);
    }
  }

}
