<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the menu link of a menu tree element.
 *
 * @todo Fix input and output context type.
 *
 * @DataProducer(
 *   id = "menu_tree_link",
 *   name = @Translation("Menu tree link"),
 *   description = @Translation("Returns the link of a menu tree element."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Menu link")
 *   ),
 *   consumes = {
 *     "element" = @ContextDefinition("any",
 *       label = @Translation("Menu link tree element")
 *     )
 *   }
 * )
 */
class MenuTreeLink extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $element
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   */
  public function resolve(MenuLinkTreeElement $element) {
    return $element->link;
  }

}
