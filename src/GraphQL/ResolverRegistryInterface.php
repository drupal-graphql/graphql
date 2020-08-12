<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

interface ResolverRegistryInterface {

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return callable|null
   *   Returns resolvedField from $value, $args, $context, $info, $field or
   *   null.
   */
  public function resolveField($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field);

  /**
   * @param $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   *   Returns resolvedType from $value, $context, $info or null.
   */
  public function resolveType($value, ResolveContext $context, ResolveInfo $info);

  /**
   * @param string $type
   * @param string $field
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *
   * @return $this
   */
  public function addFieldResolver($type, $field, ResolverInterface $resolver);

  /**
   * @param string $type
   * @param string $field
   *
   * @return callable|null
   *   Returns the FieldResolver from $type and $field or null.
   */
  public function getFieldResolver($type, $field);

  /**
   * TODO: Type resolvers should also get their own interface.
   *
   * @param string $abstract
   * @param callable $resolver
   *
   * @return $this
   */
  public function addTypeResolver($abstract, callable $resolver);

  /**
   * @param string $type
   *
   * @return callable|null
   *   Returns the TypeResolver from $type or null.
   */
  public function getTypeResolver($type);

}
