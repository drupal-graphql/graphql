<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns whether a menu link is expanded.
 *
 * @todo Fix input context type.
 */
#[DataProducer(
  id: 'menu_link_expanded',
  name: new TranslatableMarkup('Menu link expanded'),
  description: new TranslatableMarkup('Returns whether a menu link is expanded.'),
  produces: new ContextDefinition(
    data_type: 'boolean',
    label: new TranslatableMarkup('Expanded'),
  ),
  consumes: [
    'link' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Menu link'),
    ),
  ],
)]
class MenuLinkExpanded extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link): bool {
    return $link->isExpanded();
  }

}
