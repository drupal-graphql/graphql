<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL object of a menu link.
 *
 * @todo Fix input and output context type.
 */
#[DataProducer(
  id: 'menu_link_url',
  name: new TranslatableMarkup('Menu link url'),
  description: new TranslatableMarkup('Returns the URL of a menu link.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('URL'),
  ),
  consumes: [
    'link' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Menu link'),
    ),
  ],
)]
class MenuLinkUrl extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link): Url {
    return $link->getUrlObject();
  }

}
