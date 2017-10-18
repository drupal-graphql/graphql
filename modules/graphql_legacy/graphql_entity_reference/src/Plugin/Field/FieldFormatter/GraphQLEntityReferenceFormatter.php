<?php

namespace Drupal\graphql_entity_reference\Plugin\Field\FieldFormatter;

use Drupal\graphql_content\GraphQLFieldFormatterBase;

/**
 * Placeholder formatter for GraphQL entity reference fields.
 *
 * @FieldFormatter(
 *   id = "graphql_entity_reference",
 *   label = @Translation("GraphQL Entity Reference"),
 *   field_types = { "entity_reference", "entity_reference_revisions" }
 * )
 */
class GraphQLEntityReferenceFormatter extends GraphQLFieldFormatterBase {

}
