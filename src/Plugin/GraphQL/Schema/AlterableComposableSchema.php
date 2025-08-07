<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\Attribute\Schema;

/**
 * The same as ComposableSchema.
 *
 * @deprecated in graphql:5.0.0 and is removed from graphql:6.0.0 Use
 *   ComposableSchema instead, it now dispatches the schema alter events.
 * @see https://www.drupal.org/project/graphql/issues/3520035
 */
#[Schema(
  id: "alterable_composable",
  name: "Alterable composable schema"
)]
class AlterableComposableSchema extends ComposableSchema {

}
