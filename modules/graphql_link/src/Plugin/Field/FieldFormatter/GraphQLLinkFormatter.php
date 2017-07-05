<?php

namespace Drupal\graphql_link\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL link fields.
 *
 * @FieldFormatter(
 *   id = "graphql_link",
 *   label = @Translation("GraphQL Link"),
 *   field_types = { "link" }
 * )
 */
class GraphQLLinkFormatter extends GraphQLFieldFormatterBase {

}
