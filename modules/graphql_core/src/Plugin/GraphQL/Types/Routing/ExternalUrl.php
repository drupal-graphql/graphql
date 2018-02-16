<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL type for non-Drupal urls.
 *
 * @GraphQLType(
 *   id = "external_url",
 *   name = "ExternalUrl",
 *   interfaces={"Url"}
 * )
 */
class ExternalUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, $context, ResolveInfo $info) {
    return $value instanceof Url && $value->isExternal();
  }

}
