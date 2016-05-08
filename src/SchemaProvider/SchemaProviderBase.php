<?php

namespace Drupal\graphql\SchemaProvider;

use Drupal\graphql\SchemaProviderInterface;

/**
 * Abstract base class for schema providers.
 */
abstract class SchemaProviderBase implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return [];
  }
}
