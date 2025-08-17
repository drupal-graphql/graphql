<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * XML attribute data producer.
 */
#[DataProducer(
  id: "xml_attribute",
  name: new TranslatableMarkup("XML Attribute"),
  description: new TranslatableMarkup("The attribute of a DOM element."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Content")
  ),
  consumes: [
    "dom" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("The DOM element")
    ),
    "name" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("The name of the attribute")
    ),
  ]
)]
class XMLAttribute extends DataProducerPluginBase {

  /**
   * Returns the attribute value on the DOMElement.
   *
   * @param \DOMElement $dom
   *   The source (root) DOM element.
   * @param string $name
   *   The name of the attribute.
   *
   * @return string
   *   The attribute value.
   */
  public function resolve(\DOMElement $dom, string $name): string {
    return $dom->getAttribute($name);
  }

}
