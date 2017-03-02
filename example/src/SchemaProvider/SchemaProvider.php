<?php

namespace Drupal\graphql_example\SchemaProvider;

use Drupal\graphql\SchemaProvider\SchemaProviderBase;
use Drupal\graphql_example\GraphQL\Field\Root\CreatePageField;
use Drupal\graphql_example\GraphQL\Field\Root\MenuByNameField;
use Drupal\graphql_example\GraphQL\Field\Root\PageByIdField;

/**
 * Generates a GraphQL Schema.
 */
class SchemaProvider extends SchemaProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [
      new MenuByNameField(),
      new PageByIdField(),
      new CreatePageField(),
    ];
  }
}
