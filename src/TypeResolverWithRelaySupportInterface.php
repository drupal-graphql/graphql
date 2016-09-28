<?php

namespace Drupal\graphql;

interface TypeResolverWithRelaySupportInterface extends TypeResolverInterface {
  /**
   * Determines if this implementation can resolve a given type and id to an object.
   *
   * @param string $type
   *   The name of the type to be resolved.
   * @param string $id
   *   The id of the object to be resolved.
   *
   * @return bool
   *   TRUE if this implementation can resolve the given type and id, FALSE
   *   otherwise.
   */
  public function canResolveRelayNode($type, $id);

  /**
   * Resolves the provided type and id to a full object.
   *
   * @param string $type
   *   The name of the type to be resolved.
   * @param string $id
   *   The id of the object to be resolved.
   *
   * @return mixed
   *   The resolved object.
   */
  public function resolveRelayNode($type, $id);

  /**
   * Determines if this implementation can resolve the given value to a schema type.
   *
   * @param mixed $object
   *   The resolved object to have its schema type determined.
   *
   * @return bool
   *   TRUE if this implementation can resolve the given object, FALSE
   *   otherwise.
   */
  public function canResolveRelayType($object);

  /**
   * Resolves the provided object to its corresponding schema type.
   *
   * @param mixed $object
   *   The resolved object to have its schema type determined.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The determined schema type.
   */
  public function resolveRelayType($object);

  /**
   * Determines if this implementation can resolve the global Relay id for the given type and object.
   *
   * @param string $type
   *   The object type.
   * @param string $value
   *   The object identifier.
   *
   * @return bool
   *   TRUE if this implementation can resolve the given object, FALSE
   *   otherwise.
   */
  public function canResolveRelayGlobalId($type, $value);

  /**
   * Resolves the global Relay id of the given type and value.
   *
   * @param string $type
   *   The object type.
   * @param string $value
   *   The object identifier.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The determined schema type.
   */
  public function resolveRelayGlobalId($type, $value);
}