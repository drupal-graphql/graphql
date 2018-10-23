<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "xml_attribute",
 *   name = @Translation("XML Attribute"),
 *   description = @Translation("The attribute of a DOM element."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Content"),
 *   ),
 *   consumes = {
 *     "dom" = @ContextDefinition("any",
 *       label = @Translation("The DOM element")
 *     ),
 *     "name" = @ContextDefinition("string",
 *       label = @Translation("The name of the attribute")
 *     )
 *   }
 * )
 */
class XMLAttribute extends DataProducerPluginBase {

  /**
   * @param \DOMElement $dom
   *  The source (root) DOM element.
   * @param string $name
   *  The name of the attribute.
   * @return string
   */
  public function resolve(\DOMElement $dom, $name) {
    return $dom->getAttribute($name);
  }
}
