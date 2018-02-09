<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLType(
 *   id = "default_internal_url",
 *   name = "DefaultInternalUrl",
 *   interfaces = {"InternalUrl"},
 *   weight = -1
 * )
 */
class DefaultInternalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value, ResolveInfo $info = NULL) {
    return $value instanceof Url && !$value->isExternal();
  }

}
