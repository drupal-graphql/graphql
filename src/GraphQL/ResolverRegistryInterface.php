<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

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
   * Get a field resolver for the type or any of the interfaces it implements.
   *
   * This allows common functionality (such as for Edge's or Connections) to be
   * implemented for an interface and re-used on any concrete type that extends
   * it.
   *
   * This should be used instead of `getFieldResolver` unless you're certain you
   * want the resolver only for the specific type.
   *
   * @param \GraphQL\Type\Definition\Type $type
   *   The type to find a resolver for.
   * @param string $fieldName
   *   The name of the field to find a resolver for.
   *
   * @return \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null
   *   The defined resolver for the field or NULL if none exists.
   */
  public function getFieldResolverWithInheritance(Type $type, string $fieldName): ?ResolverInterface;

  /**
   * Return the field resolver for a given type and field name.
   */
  public function getFieldResolver(string $type, string $field): ?ResolverInterface;

  /**
   * Return all field resolvers in the registry.
   *
   * @return array<string, array<string, \Drupal\graphql\GraphQL\Resolver\ResolverInterface>>
   *   A nested list of resolvers, keyed by type and field name.
   */
  public function getAllFieldResolvers(): array;

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

  /**
   * Register a custom scalar definition.
   *
   * @param string $typeName
   *   The name of the scalar type as defined in the schema.
   * @param \Drupal\graphql\GraphQL\CustomScalarInterface<mixed> $scalar
   *   The custom scalar definition.
   *
   * @return $this
   */
  public function addCustomScalar(string $typeName, CustomScalarInterface $scalar): static;

  /**
   * Get a custom scalar definition by name.
   *
   * @param string $typeName
   *   The name of the scalar type.
   *
   * @return \Drupal\graphql\GraphQL\CustomScalarInterface<mixed>|null
   *   The custom scalar definition or NULL if not found.
   */
  public function getCustomScalar(string $typeName): ?CustomScalarInterface;

}
