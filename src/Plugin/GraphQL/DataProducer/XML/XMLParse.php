<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * XML loader data producer.
 */
#[DataProducer(
  id: "xml_parse",
  name: new TranslatableMarkup("XML Parse"),
  description: new TranslatableMarkup("Parses a string into an XML document."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Document")
  ),
  consumes: [
    "input" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("The input string")
    ),
  ]
)]
class XMLParse extends DataProducerPluginBase {

  /**
   * Returns a parsed XML document.
   *
   * @param string $input
   *   The source input.
   *
   * @return \DOMElement
   *   The parsed XML document element.
   */
  public function resolve(string $input): \DOMElement {
    $document = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $document->loadHTML($input);
    return $document->documentElement;
  }

}
