<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function applies($object, $context, ResolveInfo $info) {
    return $value instanceof Url && !$value->isExternal();
  }

}
