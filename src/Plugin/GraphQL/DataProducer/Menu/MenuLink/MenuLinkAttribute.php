<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * TODO: Fix input context type.
 *
 * @DataProducer(
 *   id = "menu_link_attribute",
 *   name = @Translation("Menu link attribute"),
 *   description = @Translation("Returns an attribute of a menu link."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Attribute value")
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("Menu link")
 *     ),
 *     "attribute" = @ContextDefinition("string",
 *       label = @Translation("Attribute key")
 *     )
 *   }
 * )
 */
class MenuLinkAttribute extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   * @param $attribute
   *
   * @return mixed
   */
  public function resolve(MenuLinkInterface $link, $attribute) {
    $options = $link->getOptions();
    // Certain attributes like class can be arrays. Check for that and implode them.
    $attributeValue = NestedArray::getValue($options, ['attributes', $attribute]);
    if (is_array($attributeValue)) {
      return implode(" ", $attributeValue);
    } else {
      return $attributeValue;
    }
  }

}
