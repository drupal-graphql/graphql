<?php

declare(strict_types=1);

namespace Drupal\graphql_composable\Plugin\GraphQL\Schema;

use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;

/**
 * Example of a composable schema.
 *
 * @Schema(
 *   id = "composable",
 *   name = "Composable Example schema",
 *   extensions = "composable",
 * )
 */
class ComposableSchemaExample extends ComposableSchema {

}
