<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Event\AlterSchemaDataEvent;
use Drupal\graphql\Event\AlterSchemaExtensionDataEvent;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class that can be used by schema plugins.
 *
 * @phpstan-import-type TypeConfigDecorator from \GraphQL\Utils\ASTDefinitionBuilder
 */
abstract class SdlSchemaPluginBase extends PluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface, CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * Whether the system is currently in development mode.
   */
  protected bool $inDevelopment;

  /**
   * The statically cached resolver registry.
   */
  private ?ResolverRegistryInterface $resolverRegistry = NULL;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.schema_extension'),
      $container->getParameter('graphql.config'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * SdlSchemaPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Cache\CacheBackendInterface $astCache
   *   The cache bin for caching the parsed SDL.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\graphql\Plugin\SchemaExtensionPluginManager $extensionManager
   *   The schema extension plugin manager.
   * @param array $config
   *   The service configuration.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected CacheBackendInterface $astCache,
    protected ModuleHandlerInterface $moduleHandler,
    protected SchemaExtensionPluginManager $extensionManager,
    array $config,
    protected EventDispatcherInterface $dispatcher,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->inDevelopment = !empty($config['development']);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \GraphQL\Error\Error
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSchema(): Schema {
    $document = $this->getSchemaDocument();
    $registry = $this->getResolverRegistry();

    return $this->buildSchema($document, $registry);
  }

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry(): ResolverRegistryInterface {
    // This function may be called multiple times (e.g. in Server) and thus
    // should statically cache its result.
    if ($this->resolverRegistry === NULL) {
      $registry = $this->createResolverRegistry();
      $this->registerResolvers($registry);
      $this->registerExtensionResolvers($registry);
      $this->resolverRegistry = $registry;
    }

    return $this->resolverRegistry;
  }

  /**
   * Registers base schema type and field resolvers in the shared registry.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   */
  abstract protected function registerResolvers(ResolverRegistryInterface $registry): void;

  /**
   * Register the resolvers for the extensions registered to this schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   */
  protected function registerExtensionResolvers(ResolverRegistryInterface $registry): void {
    $extensions = $this->getExtensions();
    foreach ($extensions as $extension) {
      $extension->registerResolvers($registry);
    }
  }

  /**
   * Instantiate the resolver registry.
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   *   The instantiated resolver registry without anything registered.
   */
  protected function createResolverRegistry(): ResolverRegistryInterface {
    return new ResolverRegistry();
  }

  /**
   * Get the type config decorator for the schema building.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   *
   * @return callable
   *   The type config decorator function.
   *
   * @phpstan-return TypeConfigDecorator
   */
  protected function getTypeConfigDecorator(ResolverRegistryInterface $registry): callable {
    $resolver = [$registry, 'resolveType'];

    return static function ($config, TypeDefinitionNode $type) use ($resolver, $registry) {
      if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
        $config['resolveType'] = $resolver;
      }

      if ($type instanceof ScalarTypeDefinitionNode) {
        $definition = $registry->getCustomScalar($type->name->value);
        if ($definition !== NULL) {
          $config = array_merge($config, [
            'serialize' => [$definition, 'serialize'],
            'parseValue' => [$definition, 'parseValue'],
            'parseLiteral' => [$definition, 'parseLiteral'],
          ]);
        }
      }

      return $config;
    };
  }

  /**
   * Create a GraphQL schema object from the given AST document.
   *
   * This method is private for now as the build/cache approach might change.
   */
  private function buildSchema(DocumentNode $astDocument, ResolverRegistryInterface $registry): Schema {
    // Performance: only validate the schema in development mode, skip it in
    // production on every request.
    $options = empty($this->inDevelopment) ? ['assumeValid' => TRUE] : [];
    return BuildSchema::build(
      $astDocument,
      $this->getTypeConfigDecorator($registry),
      $options
    );
  }

  /**
   * Returns the list of schema extension plugins.
   *
   * @return array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface>
   */
  protected function getExtensions(): array {
    return $this->extensionManager->getExtensions($this->getPluginId());
  }

  /**
   * Retrieves the parsed AST of the schema definition.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getSchemaDocument(): DocumentNode {
    // Only use caching of the parsed document if we aren't in development mode.
    $cid = $this->getCacheId('schema');
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($cid)) {
      return $cache->data;
    }

    $ast = $this->buildSchemaDocument(
      $this->getExtensions()
    );

    if (empty($this->inDevelopment)) {
      $this->astCache->set($cid, $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

  /**
   * Assemble the parsed schema document from its individual definitions.
   *
   * @param array<\Drupal\graphql\Plugin\SchemaExtensionPluginInterface> $extensions
   *   The list of extension plugins for this schema.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The AST of the schema.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \GraphQL\Error\SyntaxError
   */
  protected function buildSchemaDocument(array $extensions): DocumentNode {
    $baseSchemaDocument = $this->getSchemaDefinition();
    // For caching and parsing big schemas we need to disable the creation of
    // location nodes in the AST object to prevent serialization and memory
    // errors. See https://github.com/webonyx/graphql-php/issues/1164. In
    // development, we don't cache and can provide location info for debugging.
    $ast = Parser::parse($baseSchemaDocument, ['noLocation' => !$this->inDevelopment]);

    $extensionBaseAsts = array_filter(array_map(function (SchemaExtensionPluginInterface $extension) {
      $schema = $extension->getBaseDefinition();
      if ($schema === NULL) {
        return NULL;
      }
      return Parser::parse($schema, ['noLocation' => !$this->inDevelopment]);
    }, $extensions), function ($definition) {
      return !empty($definition);
    });

    $asts = [$this->getPluginId() => $ast, ...$extensionBaseAsts];
    $event = new AlterSchemaDataEvent($asts);
    $this->dispatcher->dispatch(
      $event,
      AlterSchemaDataEvent::EVENT_NAME
    );

    $asts = $event->getSchemaData();

    $extensionExtensionAsts = array_filter(array_map(function (SchemaExtensionPluginInterface $extension) {
      $schema = $extension->getExtensionDefinition();
      if ($schema === NULL) {
        return NULL;
      }
      return Parser::parse($schema, ['noLocation' => !$this->inDevelopment]);
    }, $extensions), function ($definition) {
      return !empty($definition);
    });

    // Event in order to alter the schema extension data.
    $event = new AlterSchemaExtensionDataEvent($extensionExtensionAsts);
    $this->dispatcher->dispatch(
      $event,
      AlterSchemaExtensionDataEvent::EVENT_NAME
    );

    $extensionExtensionAsts = $event->getSchemaExtensionData();

    // The asts have the plugin IDs as keys for the alter events, use
    // `array_values` to generate new keys.
    return AST::concatAST(array_merge(
      array_values($asts),
      array_values($extensionExtensionAsts),
    ));
  }

  /**
   * Retrieves the raw schema definition string.
   *
   * @return \GraphQL\Language\Source
   *   The schema definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getSchemaDefinition(): Source {
    $id = $this->getPluginId();
    $definition = $this->getPluginDefinition();
    $module = $this->moduleHandler->getModule($definition['provider']);
    $path = 'graphql/' . $id . '.graphqls';
    $file = $module->getPath() . '/' . $path;

    if (!file_exists($file)) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'The module "%s" needs to have a schema definition "%s" in its folder for "%s" to be valid.',
          $module->getName(), $path, $definition['class']));
    }

    $contents = file_get_contents($file);
    if ($contents === FALSE) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'Failed to read schema file "%s".',
          $file
        )
      );
    }

    if (trim($contents) === '') {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'Schema file "%s" may not be empty.',
          $file
        )
      );
    }

    return new Source($contents, $file);
  }

  /**
   * Returns a cache ID for the given type.
   *
   * @param string $type
   *   The cache type, e.g. 'schema' or 'full'.
   *
   * @return string
   *   The cache ID.
   */
  protected function getCacheId(string $type): string {
    // Configurable schema plugins should be cached per server since the schema
    // depends on the server configuration.
    if ($this instanceof ConfigurableInterface) {
      $configuration = $this->getConfiguration();
      $server_id = $configuration['server_id'] ?? 'default';
      return "{$type}:{$this->getPluginId()}:{$server_id}";
    }
    return "{$type}:{$this->getPluginId()}";
  }

}
