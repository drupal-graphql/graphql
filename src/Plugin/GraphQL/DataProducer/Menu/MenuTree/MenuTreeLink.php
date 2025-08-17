<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the menu link of a menu tree element.
 *
 * @todo Fix input and output context type.
 */
#[DataProducer(
  id: "menu_tree_link",
  name: new TranslatableMarkup("Menu tree link"),
  description: new TranslatableMarkup("Returns the link of a menu tree element."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Menu link"),
  ),
  consumes: [
    "element" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Menu link tree element"),
    ),
  ],
)]
class MenuTreeLink extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkTreeElement $element): MenuLinkInterface {
    return $element->link;
  }

}
