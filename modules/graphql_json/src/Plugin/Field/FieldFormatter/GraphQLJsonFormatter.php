<?php

namespace Drupal\graphql_json\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL text fields storing json content.
 *
 * @FieldFormatter(
 *   id = "graphql_json",
 *   label = @Translation("GraphQL Json"),
 *   field_types = { "text", "text_long" }
 * )
 */
class GraphQLJsonFormatter extends GraphQLFieldFormatterBase {

}
