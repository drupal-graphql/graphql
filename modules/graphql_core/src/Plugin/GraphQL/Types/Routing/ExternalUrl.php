<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
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
  public function applies($object, ResolveContext $context, ResolveInfo $info) {
    return $object instanceof Url && $object->isExternal();
  }

}
