<?php

namespace Drupal\graphql_boolean\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL boolean fields.
 *
 * @FieldFormatter(
 *   id = "graphql_boolean",
 *   label = @Translation("GraphQL Boolean"),
 *   field_types = { "boolean" }
 * )
 */
class GraphQLBooleanFormatter extends GraphQLFieldFormatterBase {

}
