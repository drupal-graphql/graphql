<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * XML child nodes content data producer.
 */
#[DataProducer(
  id: "xml_content",
  name: new TranslatableMarkup("XML Content"),
  description: new TranslatableMarkup("The content of a DOM element."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Content")
  ),
  consumes: [
    "dom" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("The DOM element")
    ),
  ]
)]
class XMLContent extends DataProducerPluginBase {

  /**
   * Returns the XML content as string.
   *
   * @param \DOMElement $dom
   *   The source (root) DOM element.
   *
   * @return string
   *   The XML content as string.
   */
  public function resolve(\DOMElement $dom): string {
    return implode('', array_map(function ($child) {
      if ($child instanceof \DOMText) {
        return $child->nodeValue;
      }
      elseif ($child instanceof \DOMElement) {
        return $child->ownerDocument->saveXML($child);
      }
    }, iterator_to_array($dom->childNodes)));
  }

}
