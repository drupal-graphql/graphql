<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an xml elements tag name.
 *
 * @GraphQLField(
 *   id = "xml_name",
 *   secure = true,
 *   type = "String",
 *   name = "name",
 *   types = { "XMLElement" }
 * )
 */
class XMLName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof \DOMElement) {
      yield $value->tagName;
    }
  }

}
