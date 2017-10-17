<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;


use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get the a specific response header of an internal or external request.
 *
 * @GraphQLField(
 *   id = "response_header",
 *   secure = true,
 *   name = "header",
 *   type = "String",
 *   parents = {"InternalResponse", "ExternalResponse"},
 *   arguments={
 *     "key" = "String"
 *   }
 * )
 */
class ResponseHeader extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Response) {
      yield $value->headers->get($args['key']);
    }

    if ($value instanceof ResponseInterface) {
      yield implode(";", $value->getHeader($args['key']));
    }
  }

}
