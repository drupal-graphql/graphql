<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;


use Drupal\graphql_core\Annotation\GraphQLField;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\HttpFoundation\Response;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get the a specific response header of an internal request.
 *
 * @GraphQLField(
 *   id = "internal_response_header",
 *   name = "header",
 *   type = "String",
 *   types = {"InternalResponse"},
 *   arguments={
 *     "key" = "String"
 *   }
 * )
 */
class InternalResponseHeader extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Response) {
      yield $value->headers->get($args['key']);
    }
  }

}
