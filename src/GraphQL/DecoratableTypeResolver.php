<?php

namespace Drupal\graphql\GraphQL;

/**
 * A decoratable type resolver to resolve GraphQL interfaces to concrete types.
 *
 * Type resolvers should extend this class so that they can be chained in
 * schema extensions plugins.
 *
 * For example with the following class defined.
 * ```php
 * class ConcreteTypeResolver extends DecoratableTypeResolver {
 *
 *   protected function resolve($object) : ?string {
 *     return $object instanceof MyType ? 'MyType' : NULL;
 *   }
 * }
 * ```
 *
 * A schema extension would call:
 * ```php
 * $registry->addTypeResolver(
 *   'InterfaceType',
 *   new ConcreteTypeResolver($registry->getTypeResolver('InterfaceType'))
 * );
 * ```
 *
 * TypeResolvers should not extend other type resolvers but always extend this
 * class directly. Classes will be called in the reverse order of being added
 * (classes added last will be called first).
 *
 * @package Drupal\thunder_gqls\GraphQL
 */
abstract class DecoratableTypeResolver {

  /**
   * The previous type resolver that was set in the chain.
   *
   * @var \Drupal\graphql\GraphQL\DecoratableTypeResolver|null
   */
  private $decorated;

  /**
   * Create a new decoratable type resolver.
   *
   * @param \Drupal\graphql\GraphQL\DecoratableTypeResolver|null $resolver
   *   The previous type resolver if any.
   */
  public function __construct(self $resolver = NULL) {
    $this->decorated = $resolver;
  }

  /**
   * Resolve the type for the provided object.
   *
   * @param mixed $object
   *   The object to resolve to a concrete type.
   *
   * @return string|null
   *   The GraphQL type name or NULL if this resolver could not determine it.
   */
  abstract protected function resolve($object) : ?string;

  /**
   * Allows this type resolver to be called by the GraphQL library.
   *
   * Takes care of chaining the various type resolvers together and invokes the
   * `resolve` method for each concrete implementation in the chain.
   *
   * @param mixed $object
   *   The object to resolve to a concrete type.
   *
   * @return string
   *   The resolved GraphQL type name.
   *
   * @throws \RuntimeException
   *   When a type was passed for which no type resolver exists in the chain.
   */
  public function __invoke($object) : string {
    $type = $this->resolve($object);
    if ($type !== NULL) {
      return $type;
    }

    if ($this->decorated !== NULL) {
      $type = $this->decorated->__invoke($object);
      if ($type !== NULL) {
        return $type;
      }
    }

    $klass = get_class($object);
    throw new \RuntimeException("Can not map instance of '${klass}' to concrete GraphQL Type.");
  }

}
