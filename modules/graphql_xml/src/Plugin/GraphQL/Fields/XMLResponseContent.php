<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\graphql_core\Plugin\GraphQL\Fields\ResponseContent;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get the response content of an internal or external request as xml document.
 *
 * @GraphQLField(
 *   id = "xml_response_content",
 *   secure = true,
 *   name = "xml",
 *   type = "XMLElement",
 *   types = {"InternalResponse", "ExternalResponse"}
 * )
 */
class XMLResponseContent extends ResponseContent {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues($value, $args, $info) as $item) {
      $document = new \DOMDocument();
      $document->loadXML($item);
      yield $document->documentElement;
    }
  }

}
