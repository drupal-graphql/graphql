<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\Plugin\SchemaPluginManager;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;

/**
 * GraphQL validation service.
 */
class Validator implements ValidatorInterface {

  /**
   * ValidateResolverController constructor.
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   */
  public function __construct(
    protected SchemaPluginManager $pluginManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateSchema(ServerInterface $server): array {
    $plugin = $this->pluginManager->getInstanceFromServer($server);
    try {
      return $plugin->getSchema()->validate();
    }
    // Catch errors that may be thrown during schema retrieval.
    catch (Error $e) {
      return [$e];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMissingResolvers(ServerInterface $server, array $ignore_types = []): array {
    $plugin = $this->pluginManager->getInstanceFromServer($server);
    $resolver_registry = $plugin->getResolverRegistry();

    try {
      $schema = $plugin->getSchema();
    }
    // In case the schema can't even be loaded we can't report anything.
    catch (Error $e) {
      return [];
    }

    $missing_resolvers = [];
    foreach ($schema->getTypeMap() as $type) {
      // We only care about concrete fieldable types. Resolvers may be defined
      // for interfaces to be available for all implementing types, but only the
      // actual resolved types need resolvers for their fields.
      if (!$type instanceof ObjectType) {
        continue;
      }

      // Skip hidden/internal/introspection types since they're handled by
      // GraphQL itself.
      if (strpos($type->name, "__") === 0) {
        continue;
      }

      if (in_array($type->name, $ignore_types, TRUE)) {
        continue;
      }

      foreach ($type->getFields() as $field) {
        if ($resolver_registry->getFieldResolverWithInheritance($type, $field->name) === NULL) {
          if (!isset($missing_resolvers[$type->name])) {
            $missing_resolvers[$type->name] = [];
          }
          $missing_resolvers[$type->name][] = $field->name;
        }
      }
    }

    return $missing_resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrphanedResolvers(ServerInterface $server, array $ignore_types = []): array {
    $plugin = $this->pluginManager->getInstanceFromServer($server);
    $resolver_registry = $plugin->getResolverRegistry();

    try {
      $schema = $plugin->getSchema();
    }
    // In case the schema can't even be loaded we can't report anything.
    catch (Error $e) {
      return [];
    }

    $orphaned_resolvers = [];
    /**
     * @var string $type_name
     * @var array $fields
     */
    foreach ($resolver_registry->getAllFieldResolvers() as $type_name => $fields) {
      if (in_array($type_name, $ignore_types, TRUE)) {
        continue;
      }

      try {
        $type = $schema->getType($type_name);
      }
      catch (InvariantViolation $_) {
        $type = NULL;
      }

      // If the type can't have any fields then our resolvers don't make sense.
      if (!$type instanceof InterfaceType &&
        !$type instanceof ObjectType &&
        !$type instanceof InputObjectType) {
        $orphaned_resolvers[$type_name] = array_keys($fields);
        continue;
      }

      foreach ($fields as $field_name => $resolver) {
        try {
          $type->getField($field_name);
        }
        catch (InvariantViolation $_) {
          if (!isset($orphaned_resolvers[$type_name])) {
            $orphaned_resolvers[$type_name] = [];
          }
          $orphaned_resolvers[$type_name][] = $field_name;
        }
      }
    }

    return $orphaned_resolvers;
  }

}
