<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a routes canonical path.
 *
 * @GraphQLField(
 *   id = "url_path",
 *   name = "path",
 *   type = "String",
 *   types = {"Url"}
 * )
 */
class Path extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      if ($value->isRouted()) {
        yield Url::fromUri('internal:/' . $value->getInternalPath())->toString();
      }
      else {
        yield $value->toString();
      }
    }
  }

}
