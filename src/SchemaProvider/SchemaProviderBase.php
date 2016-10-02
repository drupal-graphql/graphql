<?php

namespace Drupal\graphql\SchemaProvider;

/**
 * Abstract base class for schema providers.
 */
abstract class SchemaProviderBase implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

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
