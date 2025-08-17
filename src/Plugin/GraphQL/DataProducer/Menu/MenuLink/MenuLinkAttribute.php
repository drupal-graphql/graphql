<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the attribute(s) of a menu link.
 *
 * @todo Fix input context type.
 */
#[DataProducer(
  id: "menu_link_attribute",
  name: new TranslatableMarkup("Menu link attribute"),
  description: new TranslatableMarkup("Returns an attribute of a menu link."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Attribute value")
  ),
  consumes: [
    "link" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Menu link")
    ),
    "attribute" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Attribute key")
    ),
  ]
)]
class MenuLinkAttribute extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link, string $attribute): ?string {
    $options = $link->getOptions();
    // Certain attributes like class can be arrays. Check for that and implode
    // them.
    $attributeValue = NestedArray::getValue(
      $options,
      ['attributes', $attribute]
    );
    if (is_array($attributeValue)) {
      return implode(" ", $attributeValue);
    }
    else {
      return $attributeValue;
    }
  }

}
