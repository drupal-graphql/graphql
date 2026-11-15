<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * XPath query data producer.
 */
#[DataProducer(
  id: "xml_xpath",
  name: new TranslatableMarkup("XML Path"),
  description: new TranslatableMarkup("A DOM element located at a specific path."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("DOM element"),
    multiple: TRUE
  ),
  consumes: [
    "dom" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("The source DOM element")
    ),
    "query" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("The xpath query")
    ),
  ]
)]
class XMLXpath extends DataProducerPluginBase {

  /**
   * Execute the XPath query and return matching DOMElements.
   *
   * @param \DOMElement $dom
   *   The source (root) DOM element.
   * @param string $query
   *   The xpath query.
   *
   * @return list<\DOMNode>
   *   The nodes matching the XPath query.
   */
  public function resolve(\DOMElement $dom, string $query): array {
    $document = $dom->ownerDocument;
    if ($document === NULL) {
      throw new \InvalidArgumentException('The XML element has no owner document.');
    }
    $xpath = new \DOMXPath($document);
    /** @var \DOMNodeList<\DOMNode>|false $matches */
    $matches = $xpath->query($query, $dom);
    if ($matches === FALSE) {
      throw new \InvalidArgumentException('The XPath query is invalid.');
    }

    return iterator_to_array($matches, FALSE);
  }

}
