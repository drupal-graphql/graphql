<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "url_path",
 *   secure = true,
 *   name = "path",
 *   description = @Translation("The processed url path."),
 *   type = "String",
 *   response_cache_contexts = {"languages:language_url"},
 *   parents = {"Url"}
 * )
 */
class Path extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $url = $value->toString(TRUE);
      yield new CacheableValue($url->getGeneratedUrl(), [$url]);
    }
  }

}
