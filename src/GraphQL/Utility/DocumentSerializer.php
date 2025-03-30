<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Utils\AST;

/**
 * Cleans up AST recursively for serialization.
 */
class DocumentSerializer {

  /**
   * Turn the AST document to a serializable array.
   */
  public static function serializeDocument(DocumentNode $document): array {
    return static::sanitizeRecursive(AST::toArray($document));
  }

  /**
   * Recursively turn AST items into a serializable array.
   */
  public static function sanitizeRecursive(array $item): array {
    unset($item['loc']);

    foreach ($item as &$value) {
      if (is_array($value)) {
        $value = static::sanitizeRecursive($value);
      }
    }

    return $item;
  }

}
