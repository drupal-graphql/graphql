<?php

namespace Drupal\graphql_example\SchemaProvider;

use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Drupal\graphql_example\GraphQL\Field\Root\CreatePageField;
use Drupal\graphql_example\GraphQL\Field\Root\MenuByNameField;
use Drupal\graphql_example\GraphQL\Field\Root\PageByIdField;
use Youshido\GraphQL\Schema\Schema;

/**
 * Generates a GraphQL Schema.
 */
class SchemaProvider implements SchemaProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = new Schema();
    $schema->addQueryField(new MenuByNameField());
    $schema->addQueryField(new PageByIdField());
    $schema->addQueryField(new CreatePageField());

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return [];
  }
}
