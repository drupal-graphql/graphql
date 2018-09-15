<?php

namespace Drupal\graphql\Plugin;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

interface SchemaBuilderInterface {

  /**
   * Checks if a given type has any fields attached to it.
   *
   * @param string $type
   *   The name of the type.
   *
   * @return bool
   *   TRUE if the type has any fields, FALSE otherwise.
   */
  public function hasFields($type);

  /**
   * Checks if the schema has any mutations.
   *
   * @return bool
   *   TRUE if the schema has any mutations, FALSE otherwise.
   */
  public function hasMutations();

  /**
   * Checks if the schema has any subscriptions.
   *
   * @return bool
   *   TRUE if the schema has any subscriptions, FALSE otherwise.
   */
  public function hasSubscriptions();

  /**
   * Checks if the schema contains the given type.
   *
   * @param string $name
   *   The name of the type to look for in the schema.
   *
   * @return bool
   *   TRUE if the type exists in the schema, FALSE otherwise.
   */
  public function hasType($name);

  /**
   * Retrieves the fields for a given type.
   *
   * @param string $type
   *   The name of the type to retrieve the fields for.
   *
   * @return array
   *   The fields belonging to the given type.
   */
  public function getFields($type);

  /**
   * Retrieves the mutations attached to the schema.
   *
   * @return array
   *   The mutations for this schema.
   */
  public function getMutations();

  /**
   * Retrieves the subscriptions attached to the schema.
   *
   * @return array
   *   The subscriptions for this schema.
   */
  public function getSubscriptions();

  /**
   * Retrieves all type instances from the schema.
   *
   * @return array
   *   The list of type instances contained within the schema.
   */
  public function getTypes();

  /**
   * Retrieve the list of derivatives associated with a composite type.
   *
   * @param string $name
   *   The name of interface or union type.
   *
   * @return string[]
   *   The list of possible sub typenames.
   */
  public function getSubTypes($name);

  /**
   * Resolves a given value to a concrete type.
   *
   * @param string $name
   *   The name of the interface or union type.
   * @param mixed $value
   *   The value to resolve the concrete type for.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context object.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase|null
   *   The object type that applies to the given value.
   */
  public function resolveType($name, $value, ResolveContext $context, ResolveInfo $info);

  /**
   * Retrieve the type instance for a given type name.
   *
   * @param string $name
   *   The name of the type to retrieve the type instance for.
   *
   * @return \GraphQL\Type\Definition\Type
   *   The type instance corresponding to the given type name.
   */
  public function getType($name);

  /**
   * Processes a list of mutation definitions.
   *
   * @param array $mutations
   *   An array of mutation definitions.
   *
   * @return array
   *   The processed mutation definitions.
   */
  public function processMutations(array $mutations);

  /**
   * Processes a list of subscription definitions.
   *
   * @param array $subscriptions
   *   An array of subscription definitions.
   *
   * @return array
   *   The processed subscription definitions.
   */
  public function processSubscriptions(array $subscriptions);

  /**
   * Processes a list of field definitions.
   *
   * @param array $fields
   *   An array of field definitions.
   *
   * @return array
   *   The processed field definitions.
   */
  public function processFields(array $fields);

  /**
   * Processes a list of argument definitions.
   *
   * @param array $args
   *   An array of argument definitions.
   *
   * @return array
   *   The processed argument definitions.
   */
  public function processArguments(array $args);

  /**
   * Processes a optimized type definition structure.
   *
   * @param array $type
   *   The type definition with the first array element representing the name of
   *   the type and the second array element representing the list of decorators
   *   to apply to the type.
   *
   * @return \GraphQL\Type\Definition\Type
   *   The decorated type instance corresponding to the given type definition.
   */
  public function processType(array $type);

}
