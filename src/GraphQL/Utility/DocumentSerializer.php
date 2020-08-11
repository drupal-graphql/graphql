<?php

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Utils\AST;

class DocumentSerializer {

  /**
   * @param \GraphQL\Language\AST\DocumentNode $document
   *
   * @return array
   *   Returns an array containg serialized Documents
   */
  public static function serializeDocument(DocumentNode $document) {
    return static::sanitizeRecursive(AST::toArray($document));
  }

  /**
   * @param array $item
   *
   * @return array
   *   Returns an item array.
   */
  public static function sanitizeRecursive(array $item) {
    unset($item['loc']);

    foreach ($item as &$value) {
      if (is_array($value)) {
        $value = static::sanitizeRecursive($value);
      }
    }

    return $item;
  }

}
