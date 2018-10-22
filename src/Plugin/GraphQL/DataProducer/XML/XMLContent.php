<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\XML;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "xml_content",
 *   name = @Translation("XML Content"),
 *   description = @Translation("The content of a DOM element."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Content"),
 *   ),
 *   consumes = {
 *     "dom" = @ContextDefinition("any",
 *       label = @Translation("The DOM element")
 *     )
 *   }
 * )
 */
class XMLContent extends DataProducerPluginBase {

  /**
   * @param \DOMElement $dom
   *  The source (root) DOM element.
   * @param \Drupal\graphql\Plugin\GraphQL\DataProducer\XML\RefinableCacheableDependencyInterface $metadata
   * @return string
   */
  public function resolve(\DOMElement $dom, RefinableCacheableDependencyInterface $metadata) {
    return implode('', array_map(function ($child) {
      if ($child instanceof \DOMText) {
        return $child->nodeValue;
      }
      elseif ($child instanceof \DOMElement) {
        return $child->ownerDocument->saveXML($child);
      }
      else {
        return '';
      }
    }, iterator_to_array($dom->childNodes)));
  }
}
