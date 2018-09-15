<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\RedirectUrl;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\redirect\Entity\Redirect;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "redirect_url_code",
 *   secure = true,
 *   name = "code",
 *   description = @Translation("The redirect code."),
 *   type = "Int",
 *   provider="redirect",
 *   parents = {"RedirectUrl"}
 * )
 */
class Code extends FieldPluginBase {
  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Redirect) {
      yield $value->getStatusCode();
    }
  }

}
