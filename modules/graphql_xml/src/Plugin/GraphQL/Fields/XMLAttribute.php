<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an xml elements attribute value.
 *
 * @GraphQLField(
 *   id = "xml_attribute",
 *   secure = true,
 *   type = "String",
 *   name = "attribute",
 *   arguments = { "name": "String" },
 *   types = { "XMLElement" }
 * )
 */
class XMLAttribute extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof \DOMElement) {
      yield $value->getAttribute($args['name']);
    }
  }

}
