<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\Response;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Get the response content of an internal or external request.
 *
 * @GraphQLField(
 *   id = "response_content",
 *   secure = true,
 *   name = "content",
 *   type = "String",
 *   parents = {"InternalResponse", "ExternalResponse"}
 * )
 */
class ResponseContent extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Response) {
      yield $value->getContent();
    }
    else if ($value instanceof ResponseInterface) {
      yield (string) $value->getBody();
    }
  }

}
