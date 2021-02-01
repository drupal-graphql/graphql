<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\Plugin\SchemaPluginInterface;
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
   * The schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $pluginManager;

  /**
   * GraphQL logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ValidateResolverController constructor.
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   */
  public function __construct(SchemaPluginManager $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSchema(ServerInterface $server): array {
    $plugin = $this->getSchemaPlugin($server);
    return $plugin->getSchema($plugin->getResolverRegistry())->validate();
  }

  /**
   * {@inheritdoc}
   */
  public function getMissingResolvers(ServerInterface $server, array $ignore_types = []) : array {
    $plugin = $this->getSchemaPlugin($server);
    $resolver_registry = $plugin->getResolverRegistry();
    $schema = $plugin->getSchema($resolver_registry);

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
  public function getOrphanedResolvers(ServerInterface $server, array $ignore_types = []) : array {
    $plugin = $this->getSchemaPlugin($server);
    $resolver_registry = $plugin->getResolverRegistry();
    $schema = $plugin->getSchema($resolver_registry);

    if (!method_exists($resolver_registry, "getAllFieldResolvers")) {
      $this->logger->warning(
        "Could not get orphaned resolvers for @server_name as it's registry class (@klass) does not implement getAllFieldResolvers.",
        [
          '@server_name' => $server->id(),
          '@klass' => get_class($resolver_registry),
        ]
      );
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
      catch (Error $_) {
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

  /**
   * Get the schema plugin for a GraphQL server.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The GraphQL server.
   *
   * @return \Drupal\graphql\Plugin\SchemaPluginInterface
   *   A schema plugin interface.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown when no schema plugin is defined for the server.
   */
  private function getSchemaPlugin(ServerInterface $server) : SchemaPluginInterface {
    $schema_name = $server->get('schema');
    /** @var \Drupal\graphql\Plugin\SchemaPluginInterface $plugin */
    $plugin = $this->pluginManager->createInstance($schema_name);
    if ($plugin instanceof ConfigurableInterface && $config = $server->get('schema_configuration')) {
      $plugin->setConfiguration($config[$schema_name] ?? []);
    }

    return $plugin;
  }

}