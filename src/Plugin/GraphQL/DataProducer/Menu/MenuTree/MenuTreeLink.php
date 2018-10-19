<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * TODO: Fix input and output context type.
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
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $element
   *
   * @return mixed
   */
  public function resolve(MenuLinkTreeElement $element) {
    return $element->link;
  }

}
