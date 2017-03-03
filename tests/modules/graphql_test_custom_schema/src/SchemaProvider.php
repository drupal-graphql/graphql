<?php

namespace Drupal\graphql_test_custom_schema;

use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Drupal\graphql_test_custom_schema\Fields\CurrentUserField;

class SchemaProvider implements SchemaProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [
      new CurrentUserField(),
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
