<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\GraphQL\Utility\AST;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Utils\BuildSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class that can be used by schema plugins.
 */
abstract class SdlSchemaPluginBase extends PluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface, CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * The cache bin for caching the parsed SDL.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $astCache;

  /**
   * Whether the system is currently in development mode.
   *
   * @var bool
   */
  protected $inDevelopment;

  /**
   * The schema extension plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaExtensionPluginManager
   */
  protected $extensionManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Whether our internal getResolverRegistry function is called.
   *
   * We're moving from `getResolverRegistry` to `createResolverRegistry` for
   * plugin implementers, allowing `getResolverRegistry` to implement caching.
   *
   * This variable allows us to throw a deprecation error for people who still
   * need to migrate, and also allows us to change how we load extensions.
   *
   * @var bool
   *
   * @todo Remove this variable in v15.
   */
  private bool $registryLoadedModern = FALSE;

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
      $container->getParameter('graphql.config')
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
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    CacheBackendInterface $astCache,
    ModuleHandlerInterface $moduleHandler,
    SchemaExtensionPluginManager $extensionManager,
    array $config
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->inDevelopment = !empty($config['development']);
    $this->astCache = $astCache;
    $this->extensionManager = $extensionManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    // @todo Replace this by an abstract protected function in v15.
    if (!method_exists($this, "createResolverRegistry")) {
      throw new \RuntimeException("Must implement `createResolverRegistry` on " . __CLASS__ . " to return an instance of `ResolverRegistryInterface`.");
    }

    // @todo Remove this in v15.
    // In v14's suggested implementation this method is overwritten so this
    // variable wouldn't be set. If we run this method then we know people have
    // adopted to the new pattern using `createResolverRegistry` and we don't
    // need to trigger the backwards compatibility paths.
    $this->registryLoadedModern = TRUE;

    $cid = "registry:{$this->getPluginId()}";
    if (empty($this->inDevelopment) && $cached = $this->astCache->get($cid)) {
      $registry = $cached->data;
    }
    if (!isset($registry)) {
      $registry = $this->createResolverRegistry();
      foreach ($this->getExtensions() as $extension) {
        $extension->registerResolvers($registry);
      }
      if (empty($this->inDevelopment)) {
        $this->astCache->set($cid, $registry, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
      }
    }

    return $registry;
  }

  /**
   * Create a new resolver registry.
   *
   * This is where the base schema plugin should create its registry and can
   * register any base
   *
   * @todo This is a breaking change and needs to be postponed.
   * @return mixed
   */
//  abstract protected function createResolverRegistry() : ResolverRegistryInterface;

  /**
   * {@inheritdoc}
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \GraphQL\Error\Error
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSchema(ResolverRegistryInterface $registry) {
    $extensions = $this->getExtensions();

    // In the modern pattern the resolvers for the extensions will be loaded
    // once when the registry is instantiated, but if that pattern is not yet
    // implemented then we must load them now.
    // @todo Remove this code in v15.
    if (!$this->registryLoadedModern) {
      // @todo Throw deprecation error here.
      foreach ($extensions as $extension) {
        $extension->registerResolvers($registry);
      }
    }

    $cid = "schema:{$this->getPluginId()}";
    if (empty($this->inDevelopment) && $cached = $this->astCache->get($cid)) {
      $document = AST::fromArray($cached->data);
      assert($document instanceof DocumentNode);
    }
    if (!isset($document)) {
      $document = $this->buildDocument();
      if (empty($this->inDevelopment)) {
        $this->astCache->set($cid, AST::toArray($document), CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
      }
    }

    $resolver = [$registry, 'resolveType'];
    $typeConfigDecorator = static function ($config, TypeDefinitionNode $type) use ($resolver) {
      if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
        $config['resolveType'] = $resolver;
      }

      return $config;
    };
    return BuildSchema::build($document, $typeConfigDecorator);
  }

  /**
   * @return \Drupal\graphql\Plugin\SchemaExtensionPluginInterface[]
   */
  protected function getExtensions() {
    return $this->extensionManager->getExtensions($this->getPluginId());
  }

  /**
   * Build the document node from this base schema and all extensions.
   *
   * This method is responsible for collecting all the separate SDL files or a
   * dynamically generated schema into a single AST DocumentNode. It will only
   * be called in case the schema could not be loaded from the cache.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The complete document node with all the enabled extensions.
   */
  protected function buildDocument() : DocumentNode {
    $extensions = $this->getExtensions();
    $schemaBase = $this->getSchemaDocument($extensions);
    $schemaExtension = $this->getExtensionDocument($extensions);

    return AST::concatAST([$schemaBase, $schemaExtension]);
  }

  /**
   * Retrieves the parsed AST of the schema definition.
   *
   * @param \Drupal\graphql\Plugin\SchemaExtensionPluginInterface[] $extensions
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *
   * @deprecated in graphql:4.6 and is removed from graphql:5.0.0.
   *    Use SdlSchemaPluginBase::buildDocument() instead.
   */
  protected function getSchemaDocument(array $extensions = []) {
    $baseDefinition = $this->getSchemaDefinition();
    if (!$baseDefinition instanceof Source) {
      @trigger_error('Returning a ' . get_debug_type($baseDefinition) . ' from `getSchemaDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
    }
    $documents = [Parser::parse($baseDefinition)];

    foreach ($extensions as $extension) {
      $definition = $extension->getBaseDefinition();
      if (!$definition instanceof Source && $definition !== NULL) {
        @trigger_error('Returning a ' . get_debug_type($definition) . ' from `getBaseDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source|NULL instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
      }

      if (empty($definition)) {
        continue;
      }

      $documents[] = Parser::parse($definition);
    }

    return AST::concatAST($documents);
  }

  /**
   * Retrieves the parsed AST of the schema extension definitions.
   *
   * @param \Drupal\graphql\Plugin\SchemaExtensionPluginInterface[] $extensions
   *
   * @return \GraphQL\Language\AST\DocumentNode|null
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   *
   * @deprecated in graphql:4.6 and is removed from graphql:5.0.0.
   *     Use SdlSchemaPluginBase::buildDocument() instead.
   */
  protected function getExtensionDocument(array $extensions = []) {
    $documents = [];
    foreach ($extensions as $extension) {
      $definition = $extension->getExtensionDefinition();
      if (!$definition instanceof Source && $definition !== NULL) {
        @trigger_error('Returning a ' . get_debug_type($definition) . ' from `getExtensionDefinition` is deprecated in graphql:4.6 and is disallowed from graphql:5.0.0. Return \GraphQL\Language\Source|NULL instead. See https://www.drupal.org/node/', E_USER_DEPRECATED);
      }

      if (empty($definition)) {
        continue;
      }

      $documents[] = Parser::parse($definition);
    }

    return AST::concatAST($documents);
  }

  /**
   * Retrieves the raw schema definition string.
   *
   * @return string
   *   The schema definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getSchemaDefinition() {
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

    return file_get_contents($file) ?: NULL;
  }

}
