<?php

namespace Drupal\graphql_xml\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL boolean fields.
 *
 * @FieldFormatter(
 *   id = "graphql_xml",
 *   label = @Translation("GraphQL XML"),
 *   field_types = { "text", "text_long", "text_with_summary" }
 * )
 */
class GraphQLXMLFormatter extends GraphQLFieldFormatterBase {

}
