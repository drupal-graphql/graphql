<?php

namespace Drupal\graphql_test_custom_schema;

use Drupal\graphql\SchemaProviderInterface;

class SchemaProvider implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return [];
  }

  /**
   * @return array
   */
  public function getCacheTags() {
    return [];
  }
}
