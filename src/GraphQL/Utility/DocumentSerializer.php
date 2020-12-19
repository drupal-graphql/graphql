<?php

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Utils\AST;

/**
 * Cleans up AST recursively for serialization.
 */
class DocumentSerializer {

  /**
   * Turn the AST document to a serializable array.
   *
   * @param \GraphQL\Language\AST\DocumentNode $document
   *
   * @return array
   */
  public static function serializeDocument(DocumentNode $document) {
    return static::sanitizeRecursive(AST::toArray($document));
  }

  /**
   * Recursively turn AST items into a serializable array.
   *
   * @param array $item
   *
   * @return array
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
