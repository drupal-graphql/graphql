<?php

namespace Drupal\graphql_test_custom_schema;

use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Drupal\graphql_test_custom_schema\Fields\NodeByIdField;
use Youshido\GraphQL\Schema\Schema;

class SchemaProvider implements SchemaProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = new Schema();
    $schema->addQueryField(new NodeByIdField());

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return [];
  }
}
