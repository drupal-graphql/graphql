<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the description of a menu link.
 *
 * @todo Fix input context type.
 */
#[DataProducer(
  id: "menu_link_description",
  name: new TranslatableMarkup("Menu link description"),
  description: new TranslatableMarkup("Returns the description of a menu link."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Description")
  ),
  consumes: [
    "link" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Menu link")
    ),
  ],
)]
class MenuLinkDescription extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link): string {
    return (string) $link->getDescription();
  }

}
