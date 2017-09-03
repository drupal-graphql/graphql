<?php

namespace Drupal\graphql_file\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL file fields.
 *
 * @FieldFormatter(
 *   id = "graphql_file",
 *   label = @Translation("GraphQL File"),
 *   field_types = { "file" }
 * )
 */
class GraphQLFileFormatter extends GraphQLFieldFormatterBase {

}
