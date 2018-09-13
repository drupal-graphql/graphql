<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\RedirectUrl;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\redirect\Entity\Redirect;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "redirect_url_target",
 *   secure = true,
 *   name = "target",
 *   description = @Translation("The redirect target."),
 *   type = "Url",
 *   provider="redirect",
 *   parents = {"RedirectUrl"}
 * )
 */
class Target extends FieldPluginBase {
  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Redirect) {
      yield $value->getRedirectUrl();
    }
  }

}
