<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "xml_parse",
 *   name = @Translation("XML Parse"),
 *   description = @Translation("Parses a string into an XML document."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Document")
 *   ),
 *   consumes = {
 *     "input" = @ContextDefinition("string",
 *       label = @Translation("The input string")
 *     )
 *   }
 * )
 */
class XMLParse extends DataProducerPluginBase {

  /**
   * @param string $input
   *  The source input.
   * @return \DOMElement
   */
  public function resolve($input) {
    $document = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $document->loadHTML($input);
    return $document->documentElement;
  }
}
