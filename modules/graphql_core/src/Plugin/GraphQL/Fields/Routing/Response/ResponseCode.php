<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\Response;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Get the response code of an internal or external request.
 *
 * @GraphQLField(
 *   id = "response_code",
 *   secure = true,
 *   name = "code",
 *   type = "Int",
 *   parents = {"InternalResponse", "ExternalResponse"}
 * )
 */
class ResponseCode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Response) {
      yield $value->getStatusCode();
    }
    else if ($value instanceof ResponseInterface) {
      yield $value->getStatusCode();
    }
  }

}
