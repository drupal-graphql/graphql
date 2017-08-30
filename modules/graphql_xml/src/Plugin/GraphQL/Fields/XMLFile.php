<?php

namespace Drupal\graphql_xml\Plugin\GraphQL\Fields;

use Drupal\file\FileInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose xml file contents.
 *
 * @GraphQLField(
 *   id = "xml_file",
 *   name = "xml",
 *   secure = true,
 *   type = "XMLElement",
 *   types = {"File"},
 * )
 */
class XMLFile extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      if ($content = file_get_contents($value->getFileUri())) {
        $doc = new \DOMDocument();
        $doc->loadXML($content);
        yield $doc->documentElement;
      }
    }
  }

}
