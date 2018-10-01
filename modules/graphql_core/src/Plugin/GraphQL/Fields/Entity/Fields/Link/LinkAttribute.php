<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\Fields\Link;

use Drupal\Component\Utility\NestedArray;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\link\LinkItemInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      $options = $value->getUrl()->getOptions();

      // Certain attributes like class can be arrays. Check for that and implode them.
      $attributeValue = NestedArray::getValue($options, ['attributes', $args['key']]);
      if (is_array($attributeValue)) {
        yield implode(' ', $attributeValue);
      } else {
        yield $attributeValue;
      }
    }
  }

}
