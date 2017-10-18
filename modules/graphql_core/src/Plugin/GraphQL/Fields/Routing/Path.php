<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a routes canonical path.
 *
 * @GraphQLField(
 *   id = "url_path",
 *   secure = true,
 *   name = "path",
 *   type = "String",
 *   arguments = {
 *     "internal" = {
 *        "type"= "Boolean",
 *        "nullable" = true,
 *        "default" = false,
 *      }
 *   },
 *   parents = {"Url"}
 * )
 */
class Path extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      if ($value->isRouted()) {
        if (!empty($args['internal'])) {
          yield '/' . Url::fromUri('internal:/' . $value->getInternalPath())->getInternalPath();
        }
        else {
          yield Url::fromUri('internal:/' . $value->getInternalPath())->toString();
        }
      }
      else {
        yield $value->toString();
      }
    }
  }

}
