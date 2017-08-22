<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;


use Drupal\graphql_core\Annotation\GraphQLField;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get the response code of an internal or external request.
 *
 * @GraphQLField(
 *   id = "response_code",
 *   name = "code",
 *   type = "Int",
 *   types = {"InternalResponse", "ExternalResponse"}
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
