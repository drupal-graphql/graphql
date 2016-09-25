<?php

namespace Drupal\graphql_test_custom_schema;

use Drupal\graphql\SchemaProviderInterface;
use Drupal\graphql_test_custom_schema\Fields\ArticleByIdField;

class SchemaProvider implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [
      new ArticleByIdField()
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return [];
  }
}
