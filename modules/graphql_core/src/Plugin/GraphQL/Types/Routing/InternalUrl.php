<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL type for Drupal routes.
 *
 * @GraphQLType(
 *   id = "internal_url",
 *   name = "InternalUrl",
 *   interfaces={"Url"}
 * )
 */
class InternalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value, ResolveInfo $info = NULL) {
    return $value instanceof Url && !$value->isExternal();
  }

}
