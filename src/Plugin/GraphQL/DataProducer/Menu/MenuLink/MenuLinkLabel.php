<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the label of a menu link.
 *
 * @todo Fix input context type.
 *
 * @DataProducer(
 *   id = "menu_link_label",
 *   name = @Translation("Menu link label"),
 *   description = @Translation("Returns the label of a menu link."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Label")
 *   ),
 *   consumes = {
 *     "link" = @ContextDefinition("any",
 *       label = @Translation("Menu link")
 *     )
 *   }
 * )
 */
class MenuLinkLabel extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link): string {
    return $link->getTitle();
  }

}
