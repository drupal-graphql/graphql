<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;


use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Youshido\GraphQL\Execution\ResolveInfo;

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
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Response) {
      yield $value->getStatusCode();
    }

    if ($value instanceof ResponseInterface) {
      yield $value->getStatusCode();
    }
  }

}
