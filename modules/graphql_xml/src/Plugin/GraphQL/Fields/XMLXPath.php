<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Evaluate a XPath query on the current element.
 *
 * @GraphQLField(
 *   id = "xml_xpath",
 *   secure = true,
 *   type = "XMLElement",
 *   multi = true,
 *   arguments = {
 *     "query" = "String"
 *   },
 *   name = "xpath",
 *   types = { "XMLElement" }
 * )
 */
class XMLXPath extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof \DOMElement) {
      $xpath = new \DOMXPath($value->ownerDocument);
      foreach ($xpath->query($args['query'], $value) as $item) {
        yield $item;
      }
    }
  }

}
