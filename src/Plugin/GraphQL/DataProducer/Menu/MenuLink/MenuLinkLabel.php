<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the label of a menu link.
 *
 * @todo Fix input context type.
 */
#[DataProducer(
  id: "menu_link_label",
  name: new TranslatableMarkup("Menu link label"),
  description: new TranslatableMarkup("Returns the label of a menu link."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Label")
  ),
  consumes: [
    "link" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Menu link")
    ),
  ],
)]
class MenuLinkLabel extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @return string
   *   The menu link title.
   */
  public function resolve(MenuLinkInterface $link): string {
    return (string) $link->getTitle();
  }

}
