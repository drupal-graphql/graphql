<?php

namespace Drupal\graphql\Entity;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\graphql\GraphQL\Execution\ExecutionResult as CacheableExecutionResult;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use GraphQL\Server\OperationParams;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Server\ServerConfig;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\Helper;
use GraphQL\Server\RequestError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Validator\DocumentValidator;

/**
 * @ConfigEntityType(
 *   id = "graphql_server",
 *   label = @Translation("Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\graphql\Controller\ServerListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\graphql\Form\ServerForm",
 *       "create" = "Drupal\graphql\Form\ServerForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "graphql_servers",
 *   admin_permission = "administer graphql configuration",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "schema",
 *     "schema_configuration",
 *     "endpoint",
 *     "debug",
 *     "caching",
 *     "batching"
 *   },
 *   links = {
 *     "collection" = "/admin/config/graphql/servers",
 *     "create-form" = "/admin/config/graphql/servers/create",
 *     "edit-form" = "/admin/config/graphql/servers/manage/{graphql_server}",
 *     "delete-form" = "/admin/config/graphql/servers/manage/{graphql_server}/delete"
 *   }
 * )
 */
class Server extends ConfigEntityBase implements ServerInterface {
  use DependencySerializationTrait;

  /**
   * The server's machine-readable name.
   *
   * @var string
   */
  public $name;

  /**
   * The server's human-readable name.
   *
   * @var string
   */
  public $label;

  /**
   * The server's schema.
   *
   * @var string
   */
  public $schema;

  /**
   * Schema configuration.
   *
   * @var array
   */
  public $schema_configuration = [];

  /**
   * Whether the server is in debug mode.
   *
   * @var boolean
   */
  public $debug = FALSE;

  /**
   * Whether the server should cache its results.
   *
   * @var boolean
   */
  public $caching = TRUE;

  /**
   * Whether the server allows query batching.
   *
   * @var boolean
   */
  public $batching = TRUE;

  /**
   * The server's endpoint.
   *
   * @var string
   */
  public $endpoint;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function executeOperation(OperationParams $operation) {
    $previous = Executor::getImplementationFactory();
    Executor::setImplementationFactory([\Drupal::service('graphql.executor'), 'create']);

    try {
      $config = $this->configuration();
      $result = (new Helper())->executeOperation($config, $operation);

      // In case execution fails before the execution stage, we have to wrap the
      // result object here.
      if (!($result instanceof CacheableExecutionResult)) {
        $result = new CacheableExecutionResult($result->data, $result->errors, $result->extensions);
        $result->mergeCacheMaxAge(0);
      }
    }
    finally {
      Executor::setImplementationFactory($previous);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function executeBatch($operations) {
    // We can't leverage parallel processing of batched queries because of the
    // contextual properties of Drupal (e.g. language manager, current user).
    return array_map(function (OperationParams $operation) {
      return $this->executeOperation($operation);
    }, $operations);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function configuration() {
    $params = \Drupal::getContainer()->getParameter('graphql.config');
    /** @var \Drupal\graphql\Plugin\SchemaPluginManager $manager */
    $manager = \Drupal::service('plugin.manager.graphql.schema');
    $schema = $this->get('schema');

    /** @var \Drupal\graphql\Plugin\SchemaPluginInterface $plugin */
    $plugin = $manager->createInstance($schema);
    if ($plugin instanceof ConfigurableInterface && $config = $this->get('schema_configuration')) {
      $plugin->setConfiguration($config[$schema] ?? []);
    }

    // Create the server config.
    $registry = $plugin->getResolverRegistry();
    $server = ServerConfig::create();
    $server->setDebug(!!$this->get('debug'));
    $server->setQueryBatching(!!$this->get('batching'));
    $server->setValidationRules($this->getValidationRules());
    $server->setPersistentQueryLoader($this->getPersistedQueryLoader());
    $server->setContext($this->getContext($plugin, $params));
    $server->setFieldResolver($this->getFieldResolver($registry));
    $server->setSchema($plugin->getSchema($registry));
    $server->setPromiseAdapter(new SyncPromiseAdapter());

    return $server;
  }

  /**
   * TODO: Handle this through configuration (e.g. a context value).
   *
   * Returns to root value to use when resolving queries against the schema.
   *
   * May return a callable to resolve the root value at run-time based on the
   * provided query parameters / operation.
   *
   * @code
   *
   * public function getRootValue() {
   *   return function (OperationParams $params, DocumentNode $document, $operation) {
   *     // Dynamically return a root value based on the current query.
   *   };
   * }
   *
   * @endcode
   *
   * @return mixed|callable
   *   The root value for query execution or a callable factory.
   */
  protected function getRootValue() {
    return NULL;
  }

  /**
   * Returns the context object to use during query execution.
   *
   * May return a callable to instantiate a context object for each individual
   * query instead of a shared context. This may be useful e.g. when running
   * batched queries where each query operation within the same request should
   * use a separate context object.
   *
   * The returned value will be passed as an argument to every type and field
   * resolver during execution.
   *
   * @code
   *
   * public function getContext() {
   *   $shared = ['foo' => 'bar'];
   *
   *   return function (OperationParams $params, DocumentNode $document, $operation) use ($shared) {
   *     $private = ['bar' => 'baz'];
   *
   *     return new MyContext($shared, $private);
   *   };
   * }
   *
   * @endcode
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginInterface $schema
   *   The schema plugin instance.
   * @param array $config
   *
   * @return mixed|callable
   *   The context object for query execution or a callable factory.
   */
  protected function getContext(SchemaPluginInterface $schema, array $config) {
    // Each document (e.g. in a batch query) gets its own resolve context. This
    // allows us to collect the cache metadata and contextual values (e.g.
    // inheritance for language) for each query separately.
    return function (OperationParams $params, DocumentNode $document, $type) use ($schema, $config) {
      $context = new ResolveContext($this, $params, $document, $type, $config);
      $context->addCacheTags(['graphql_response']);
      if ($this instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($this);
      }

      if ($schema instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($schema);
      }

      return $context;
    };
  }

  /**
   * TODO: Handle this through configuration on the server.
   *
   * Returns the default field resolver.
   *
   * Fields that don't explicitly declare a field resolver will use this one
   * as a fallback.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   *
   * @return null|callable
   *   The default field resolver.
   */
  protected function getFieldResolver(ResolverRegistryInterface $registry) {
    return function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($registry) {
      $field = new FieldContext($context, $info);
      $result = $registry->resolveField($value, $args, $context, $info, $field);
      return DeferredUtility::applyFinally($result, function ($result) use ($field, $context) {
        if ($result instanceof CacheableDependencyInterface) {
          $field->addCacheableDependency($result);
        }

        $context->addCacheableDependency($field);
      });
    };
  }

  /**
   * Returns the error formatter.
   *
   * Allows to replace the default error formatter with a custom one. It is
   * essential when there is a need to adjust error format, for instance
   * to add an additional fields or remove some of the default ones.
   *
   * @return mixed|callable
   *   The error formatter.
   *
   * @see \GraphQL\Error\FormattedError::prepareFormatter
   */
  protected function getErrorFormatter() {
    return function (Error $error) {
      return FormattedError::createFromException($error);
    };
  }

  /**
   * TODO: Handle this through configurable plugins on the server.
   *
   * Returns the error handler.
   *
   * Allows to replace the default error handler with a custom one. For example
   * when there is a need to handle specific errors differently.
   *
   * @return mixed|callable
   *   The error handler.
   *
   * @see \GraphQL\Executor\ExecutionResult::toArray
   */
  protected function getErrorHandler() {
    return function (array $errors, callable $formatter) {
      return array_map($formatter, $errors);
    };
  }

  /**
   * TODO: Handle this through configurable plugins on the server.
   *
   * Returns a callable for loading persisted queries.
   *
   * @return callable
   *   The persisted query loader.
   */
  protected function getPersistedQueryLoader() {
    return function ($id, OperationParams $params) {
      throw new RequestError('Persisted queries are currently not supported');
    };
  }

  /**
   * TODO: Handle this through configurable plugins on the server.
   *
   * Returns the validation rules to use for the query.
   *
   * May return a callable to allow the server to decide the validation rules
   * independently for each query operation.
   *
   * @code
   *
   * public function getValidationRules() {
   *   return function (OperationParams $params, DocumentNode $document, $operation) {
   *     if (isset($params->queryId)) {
   *       // Assume that pre-parsed documents are already validated. This allows
   *       // us to store pre-validated query documents e.g. for persisted queries
   *       // effectively improving performance by skipping run-time validation.
   *       return [];
   *     }
   *
   *     return array_values(DocumentValidator::defaultRules());
   *   };
   * }
   *
   * @endcode
   *
   * @return array|callable
   *   The validation rules or a callable factory.
   */
  protected function getValidationRules() {
    return function (OperationParams $params, DocumentNode $document, $operation) {
      if (isset($params->queryId)) {
        // Assume that pre-parsed documents are already validated. This allows
        // us to store pre-validated query documents e.g. for persisted queries
        // effectively improving performance by skipping run-time validation.
        return [];
      }

      return array_values(DocumentValidator::defaultRules());
    };
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }
}
