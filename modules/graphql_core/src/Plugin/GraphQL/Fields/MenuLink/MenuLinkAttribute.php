<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\MenuLink;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve specific attributes of a menu link.
 *
 * @GraphQLField(
 *   id = "menu_link_attribute",
 *   secure = true,
 *   name = "attribute",
 *   type = "String",
 *   parents = {"MenuLink"},
 *   arguments = {
 *     "key" = "String!"
 *   }
 * )
 */
class MenuLinkAttribute extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      $options = $value->link->getOptions();

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
