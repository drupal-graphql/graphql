<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Routing;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\redirect\Entity\Redirect;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL type for redirects.
 *
 * @GraphQLType(
 *   id = "redirect_url",
 *   name = "RedirectUrl",
 *   interfaces={"Url"},
 *   provider="redirect",
 * )
 */
class RedirectUrl extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveContext $context, ResolveInfo $info) {
    return $object instanceof Redirect;
  }

}
