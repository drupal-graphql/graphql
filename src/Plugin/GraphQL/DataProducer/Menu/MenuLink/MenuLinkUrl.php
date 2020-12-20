<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL object of a menu link.
 *
 * @todo Fix input and output context type.
 *
 * @DataProducer(
 *   id = "menu_link_url",
 *   name = @Translation("Menu link url"),
 *   description = @Translation("Returns the URL of a menu link."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("URL")
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("Menu link")
 *     )
 *   }
 * )
 */
class MenuLinkUrl extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *
   * @return \Drupal\Core\Url
   */
  public function resolve(MenuLinkInterface $link) {
    return $link->getUrlObject();
  }

}
