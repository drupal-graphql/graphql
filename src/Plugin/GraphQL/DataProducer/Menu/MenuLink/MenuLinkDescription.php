<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * TODO: Fix input context type.
 *
 * @DataProducer(
 *   id = "menu_link_description",
 *   name = @Translation("Menu link description"),
 *   description = @Translation("Returns the description of a menu link."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Description")
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("Menu link")
 *     )
 *   }
 * )
 */
class MenuLinkDescription extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *
   * @return mixed
   */
  public function resolve(MenuLinkInterface $link) {
    return $link->getDescription();
  }

}
