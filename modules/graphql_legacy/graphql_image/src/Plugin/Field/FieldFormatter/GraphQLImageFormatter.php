<?php

namespace Drupal\graphql_image\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL image fields.
 *
 * @FieldFormatter(
 *   id = "graphql_image",
 *   label = @Translation("GraphQL Image"),
 *   field_types = { "image" }
 * )
 */
class GraphQLImageFormatter extends GraphQLFieldFormatterBase {

}
