<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an xml elements inner content string.
 *
 * @GraphQLField(
 *   id = "xml_content",
 *   type = "String",
 *   name = "content",
 *   types = { "XMLElement" }
 * )
 */
class XMLContent extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof \DOMElement) {
      $content = '';
      foreach ($value->childNodes as $child) {
        if ($child instanceof \DOMText) {
          $content .= $child->nodeValue;
        }
        elseif ($child instanceof \DOMElement) {
          $content .= $child->ownerDocument->saveXML($child);
        }
      }
      yield $content;
    }
  }

}
