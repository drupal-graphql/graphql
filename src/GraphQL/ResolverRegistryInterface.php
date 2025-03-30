<?php

declare(strict_types=1);

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
   */
  public function resolveField(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed;

  /**
   * Resolve a type.
   */
  public function resolveType(mixed $value, ResolveContext $context, ResolveInfo $info): ?string;

  /**
   * Add a field resolver for a certain type.
   */
  public function addFieldResolver(string $type, string $field, ResolverInterface $resolver): static;

  /**
   * Return the field resolver for a given type and field name.
   */
  public function getFieldResolver(string $type, string $field): ?ResolverInterface;

  /**
   * Add a type resolver.
   *
   * @todo Type resolvers should also get their own interface.
   */
  public function addTypeResolver(string $abstract, callable $resolver): static;

  /**
   * Get the resolver for a given type name.
   */
  public function getTypeResolver(string $type): ?callable;

}
