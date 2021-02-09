<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Defines a registry to resolve any field in the GraphQL schema tree.
 */
interface ResolverRegistryInterface {

  /**
   * Resolve a field.
   *
   * @param mixed $value
   * @param mixed $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return callable|null
   */
  public function resolveField($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field);

  /**
   * Resolve a type.
   *
   * @param mixed $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  public function resolveType($value, ResolveContext $context, ResolveInfo $info);

  /**
   * Add a field resolver for a certain type.
   *
   * @param string $type
   * @param string $field
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *
   * @return $this
   */
  public function addFieldResolver($type, $field, ResolverInterface $resolver);

  /**
   * Return the field resolver for a given type and field name.
   *
   * @param string $type
   * @param string $field
   *
   * @return callable|null
   */
  public function getFieldResolver($type, $field);

  /**
   * Add a type resolver.
   *
   * @todo Type resolvers should also get their own interface.
   *
   * @param string $abstract
   * @param callable $resolver
   *
   * @return $this
   */
  public function addTypeResolver($abstract, callable $resolver);

  /**
   * Get the resolver for a given type name.
   *
   * @param string $type
   *
   * @return callable|null
   */
  public function getTypeResolver($type);

}
