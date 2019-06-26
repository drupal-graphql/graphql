<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;

interface ResolverRegistryInterface {

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return callable|null
   */
  public function resolveField($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field);

  /**
   * @param $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  public function resolveType($value, ResolveContext $context, ResolveInfo $info);

}