<?php
/**
 * @file
 * Wrapper class for values resolved through GraphQL resolvers, which includes
 * Drupal cache metadata.
 */

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\CacheableMetadata;

class ResolvedValue extends CacheableMetadata {

  /**
   * @var mixed The actual value being wrapped.
   */
  protected $value;

  public function __construct($value = '', $metadata = NULL) {
    $this->setValue($value);
    if ($metadata instanceof CacheableMetadata) {
      $this->merge($metadata);
    }
  }

  public function setValue($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

}