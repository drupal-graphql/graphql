<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Error\InvariantViolation;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\BuildSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @param $config
   *   The service configuration.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    $configuration,
    $pluginId,
    $pluginDefinition,
    CacheBackendInterface $astCache,
    $config
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->inDevelopment = !empty($config['development']);
    $this->astCache = $astCache;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return BuildSchema::build($this->getSchemaDocument(), function ($config, TypeDefinitionNode $type) {
      if ($type instanceof InterfaceTypeDefinitionNode || $type instanceof UnionTypeDefinitionNode) {
        $config['resolveType'] = $this->getTypeResolver($type);
      }

      return $config;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function validateSchema() {
    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = \Drupal::service('messenger');

    try {
      $schema = $this->getSchema();
      $schema->assertValid();
    }
    catch (SyntaxError $error) {
      $messenger->addError(sprintf('Syntax error in schema: %s', $error->getMessage()));
      return FALSE;
    }
    catch (InvariantViolation $error) {
      $messenger->addError(sprintf('Schema validation error: %s', $error->getMessage()));
      return FALSE;
    }
    catch (Error $error) {
      $messenger->addError(sprintf('Schema validation error: %s', $error->getMessage()));
      return FALSE;
    }

    $registry = $this->getResolverRegistry();
    if ($messages = $registry->validateCompliance($schema)) {
      foreach ($messages as $message) {
        $messenger->addError($message);
      }

      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRootValue() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $registry = $this->getResolverRegistry();

    // Each document (e.g. in a batch query) gets its own resolve context. This
    // allows us to collect the cache metadata and contextual values (e.g.
    // inheritance for language) for each query separately.
    return function ($params, $document, $operation) use ($registry) {
      $context = new ResolveContext(['registry' => $registry]);
      $context->addCacheTags(['graphql_response']);
      if ($this instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($this);
      }

      return $context;
    };
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldResolver() {
    return function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      return $context->getGlobal('registry')->resolveField($value, $args, $context, $info);
    };
  }

  /**
   * Resolves the name of concrete type at run-time.
   *
   * @param \GraphQL\Language\AST\TypeDefinitionNode $type
   *   An abstract type to resolve the concrete type for.
   *
   * @return \Closure
   *   The run-time type resolver.
   */
  protected function getTypeResolver(TypeDefinitionNode $type) {
    return function ($value, ResolveContext $context, ResolveInfo $info) {
      return $context->getGlobal('registry')->resolveType($value, $context, $info);
    };
  }

  /**
   * Retrieves the parsed AST of the schema definition.
   *
   * @return \GraphQL\Language\AST\DocumentNode
   *   The parsed schema document.
   */
  protected function getSchemaDocument() {
    // Only use caching of the parsed document if we aren't in development mode.
    if (empty($this->inDevelopment) && $cache = $this->astCache->get($this->getPluginId())) {
      return $cache->data;
    }

    $ast = Parser::parse($this->getSchemaDefinition());
    if (!empty($this->inDevelopment)) {
      $this->astCache->set($this->getPluginId(), $ast, CacheBackendInterface::CACHE_PERMANENT, ['graphql']);
    }

    return $ast;
  }

  /**
   * Retrieves the error formatter.
   *
   * By default uses the graphql error formatter.
   *
   * @see \GraphQL\Error\FormattedError::prepareFormatter
   *
   * @see https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
   *
   * @return \Closure
   *   Error formatter.
   */
  public function getErrorFormatter() {
    return function (Error $error) {
      return FormattedError::createFromException($error);
    };
  }

  /**
   * Retrieves the error handler.
   *
   * By default uses the default graphql error handler.
   *
   * @see \GraphQL\Executor\ExecutionResult::toArray
   *
   * @see https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
   *
   * @return \Closure
   *   Error handler.
   */
  public function getErrorHandler() {
    return function (array $errors, callable $formatter) {
      return array_map($formatter, $errors);
    };
  }

  /**
   * Retrieves the resolver registry.
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   *   The resolver registry.
   */
  abstract protected function getResolverRegistry();

  /**
   * Retrieves the raw schema definition string.
   *
   * @return string
   *   The schema definition.
   */
  abstract protected function getSchemaDefinition();

}
