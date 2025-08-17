<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns a list of menu links under the menu tree element.
 *
 * @todo Fix input and output context type.
 */
#[DataProducer(
  id: 'menu_tree_subtree',
  name: new TranslatableMarkup('Menu tree subtree'),
  description: new TranslatableMarkup('Returns the subtree of a menu tree element.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('Menu link tree element'),
    multiple: TRUE,
  ),
  consumes: [
    'element' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Menu link tree element'),
    ),
  ],
)]
class MenuTreeSubtree extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @return array<\Drupal\Core\Menu\MenuLinkTreeElement>
   *   An array of enabled menu link tree elements in the subtree.
   */
  public function resolve(MenuLinkTreeElement $element): array {
    return array_filter($element->subtree, function (MenuLinkTreeElement $item) {
      return $item->link->isEnabled();
    });
  }

}
