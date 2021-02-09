<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Defines how to resolve a value for a given field.
 */
interface ResolverInterface {

  /**
   * Resolve values for the fields.
   *
   * @param mixed $value
   * @param mixed $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return mixed
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field);

}
