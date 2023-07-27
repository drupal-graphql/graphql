<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\PhpStorage\PhpStorageInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Error\Error;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class that can be used by schema plugins.
 */
abstract class SdlSchemaPluginBase extends PluginBase implements SchemaPluginInterface, ContainerFactoryPluginInterface, CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * The file cache for caching the parsed SDL.
   *
   * @var \Drupal\Component\PhpStorage\PhpStorageInterface
   */
  protected $documentCache;

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
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.document'),
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
   * @param \Drupal\Component\PhpStorage\PhpStorageInterface $documentCache
   *   The file cache for caching the parsed SDL.
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
    array                        $configuration,
                                 $pluginId,
    array                        $pluginDefinition,
    PhpStorageInterface          $documentCache,
    ModuleHandlerInterface       $moduleHandler,
    SchemaExtensionPluginManager $extensionManager,
    array                        $config
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->inDevelopment = !empty($config['development']);
    $this->documentCache = $documentCache;
    $this->extensionManager = $extensionManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \GraphQL\Error\Error
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSchema(ResolverRegistryInterface $registry) {
    $extensions = $this->getExtensions();

    // TODO: The fact that the registry gets extended here means we can't cache
    //   all of it easily
    foreach ($extensions as $extension) {
      $extension->registerResolvers($registry);
    }

    // TODO: We might need to think about the naming to work with multiple webheads and configs?
    $resolver = [$registry, 'resolveType'];
    $document = empty($this->inDevelopment) ? $this->loadCachedDocument() : NULL;
    if ($document !== NULL) {
      return BuildSchema::build($document, function ($config, TypeDefinitionNode $type) use ($resolver) {
        if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
          $config['resolveType'] = $resolver;
        }

        return $config;
      });
    }

    $schema = BuildSchema::build($this->getBaseSchemaAst(), function ($config, TypeDefinitionNode $type) use ($resolver) {
      if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
        $config['resolveType'] = $resolver;
      }

      return $config;
    });

    /** @var DocumentNode $extensionAst */
    foreach ($this->getExtensionAsts($extensions) as $extensionAst) {
      $schema = SchemaExtender::extend($schema, $extensionAst);
    }

    // This does a full schema load, which is slow. But it's the most correct
    // way to ensure we can get a cacheable AST that's quick.
    // TODO: Move this to a drush command that can precalculate this.
    $schemaDefinitions = [$schema->getAstNode(), ...$schema->extensionASTNodes];
    foreach ($schema->getTypeMap() as $type) {
      if ($type->astNode !== NULL) {
        $schemaDefinitions[] = $type->astNode;
      }
      foreach ($type->extensionASTNodes ?? [] as $extensionNode) {
        $schemaDefinitions[] = $extensionNode;
      }
    }
    $ast = new DocumentNode(
      ['definitions' => new NodeList($schemaDefinitions)]
    );
    $this->storeCachedDocument($ast);

    return $schema;
  }

  private function loadCachedDocument() : ?Node {
    if ($this->documentCache->load($this->getPluginId())) {
      return AST::fromArray(__do_get_schema());
    }
    return NULL;
  }

  private function storeCachedDocument(Node $document) : bool {
    return $this->documentCache->save($this->getPluginId(), "<?php\nfunction __do_get_schema() { return " . var_export(AST::toArray($document), true) . "; }");
  }

  /**
   * @return \Drupal\graphql\Plugin\SchemaExtensionPluginInterface[]
   */
  protected function getExtensions() {
    return $this->extensionManager->getExtensions($this->getPluginId());
  }

  protected function getBaseSchemaAst() {
    $base_source = $this->getSchemaDefinition();
    if (is_string($base_source)) {
      @trigger_error("Returning a string from " . __CLASS__ . "::getSchemaDefinition is deprecated. Return an instance of Source or NULL instead.");
      $base_source = new Source($base_source, __CLASS__);
    }
    return Parser::parse($base_source ?? "");
  }

  protected function getExtensionAsts(array $extensions = []) {
    $extension_base_asts = [];
    $extension_extend_asts = [];
    foreach ($extensions as $id => $extension) {
      $base_definition = $extension->getBaseDefinition();
      if (is_string($base_definition)) {
        @trigger_error("Returning a string from " . get_class($extension) . "::getBaseDefinition is deprecated. Return an instance of Source or NULL instead.");
        $base_definition = new Source($base_definition, $id . "_base");
      }
      if ($base_definition !== NULL) {
        $extension_base_asts[] = Parser::parse($base_definition);
      }

      $extend_definition = $extension->getExtensionDefinition();
      if (is_string($extend_definition)) {
        @trigger_error("Returning a string from " . get_class($extension) . "::getExtensionDefinition is deprecated. Return an instance of Source or NULL instead.");
        $extend_definition = new Source($extend_definition, $id . "_extend");
      }
      if ($extend_definition !== NULL) {
        $extension_extend_asts[] = Parser::parse($extend_definition);
      }
    }

    return [...$extension_base_asts, ...$extension_extend_asts];
  }

  /**
   * Retrieves the parsed AST of the schema definition.
   *
   * @param array $extensions
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getSchemaDocument(array $extensions = []) {

    $extensions = array_filter(
      array_map(
        function (SchemaExtensionPluginInterface $extension) {
          $definition = $extension->getBaseDefinition();
          if (is_string($definition)) {
            @trigger_error("Returning a string from " . get_class($extension) . "::getBaseDefinition is deprecated. Return an instance of Source or NULL instead.");
            $definition = new Source($definition);
          }
          return $definition;
        },
        $extensions
      ),
      function ($definition) {
        return !empty($definition);
      }
    );


    $schema = array_merge([$this->getSchemaDefinition()], $extensions);
    $ast = Parser::parse(implode("\n\n", $schema));

    return $ast;
  }

  /**
   * Retrieves the parsed AST of the schema extension definitions.
   *
   * @param array $extensions
   *
   * @return \GraphQL\Language\AST\DocumentNode|null
   *   The parsed schema document.
   *
   * @throws \GraphQL\Error\SyntaxError
   */
  protected function getExtensionDocument(array $extensions = []) {
    $extensions = array_filter(array_map(function (SchemaExtensionPluginInterface $extension) {
      return $extension->getExtensionDefinition();
    }, $extensions), function ($definition) {
      return !empty($definition);
    });

    $ast = !empty($extensions) ? Parser::parse(implode("\n\n", $extensions)) : NULL;

    return $ast;
  }

  /**
   * Retrieves the raw schema definition string.
   *
   * @return \GraphQL\Language\Source|string
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

    $contents = file_get_contents($file);
    return $contents ? new Source($contents, $file) : NULL;
  }

}
