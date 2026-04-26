<?php

declare(strict_types=1);

namespace Drupal\graphql\Entity;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Controller\ServerListBuilder;
use Drupal\graphql\Form\PersistedQueriesForm;
use Drupal\graphql\Form\ServerForm;
use Drupal\graphql\GraphQL\Execution\ExecutionResult;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;
use Drupal\graphql\Plugin\SchemaPluginInterface;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\Helper;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;

/**
 * The main GraphQL configuration and request entry point.
 *
 * Multiple GraphQL servers can be defined on different routing paths with
 * different GraphQL schemas.
 *
 * @ConfigEntityType(
 *   id = "graphql_server",
 *   label = @Translation("Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\graphql\Controller\ServerListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\graphql\Form\ServerForm",
 *       "create" = "Drupal\graphql\Form\ServerForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "persisted_queries" = "Drupal\graphql\Form\PersistedQueriesForm"
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
 *     "persisted_queries_settings",
 *     "endpoint",
 *     "debug_flag",
 *     "caching",
 *     "batching",
 *     "disable_introspection",
 *     "query_depth",
 *     "query_complexity"
 *   },
 *   links = {
 *     "collection" = "/admin/config/graphql/servers",
 *     "create-form" = "/admin/config/graphql/servers/create",
 *     "edit-form" = "/admin/config/graphql/servers/manage/{graphql_server}",
 *     "delete-form" = "/admin/config/graphql/servers/manage/{graphql_server}/delete",
 *     "persisted_queries-form" = "/admin/config/graphql/servers/manage/{graphql_server}/persisted_queries",
 *   }
 * )
 */
#[ConfigEntityType(
  id: "graphql_server",
  label: new TranslatableMarkup("Server"),
  handlers: [
    "list_builder" => ServerListBuilder::class,
    "form" => [
      "edit" => ServerForm::class,
      "create" => ServerForm::class,
      "delete" => EntityDeleteForm::class,
      "persisted_queries" => PersistedQueriesForm::class,
    ],
  ],
  config_prefix: "graphql_servers",
  admin_permission: "administer graphql configuration",
  entity_keys: [
    "id" => "name",
    "label" => "label",
  ],
  config_export: [
    "name",
    "label",
    "schema",
    "schema_configuration",
    "persisted_queries_settings",
    "endpoint",
    "debug_flag",
    "caching",
    "batching",
    "disable_introspection",
    "query_depth",
    "query_complexity",
  ],
  links: [
    "collection" => "/admin/config/graphql/servers",
    "create-form" => "/admin/config/graphql/servers/create",
    "edit-form" => "/admin/config/graphql/servers/manage/{graphql_server}",
    "delete-form" => "/admin/config/graphql/servers/manage/{graphql_server}/delete",
    "persisted_queries-form" => "/admin/config/graphql/servers/manage/{graphql_server}/persisted_queries",
  ]
)]
class Server extends ConfigEntityBase implements ServerInterface {
  use DependencySerializationTrait;

  /**
   * The server's machine-readable name.
   */
  public string $name;

  /**
   * The server's human-readable name.
   */
  public ?string $label;

  /**
   * The ID of the schema plugin used by this server.
   */
  public string $schema;

  /**
   * Schema configuration.
   */
  public array $schema_configuration = [];

  /**
   * The debug settings for this server.
   *
   * @see \GraphQL\Error\DebugFlag
   */
  public int $debug_flag = DebugFlag::NONE;

  /**
   * Whether the server should cache its results.
   */
  public bool $caching = TRUE;

  /**
   * Whether the server allows query batching.
   */
  public bool $batching = TRUE;

  /**
   * Whether to disable query introspection.
   */
  public bool $disable_introspection = FALSE;

  /**
   * The maximum allowed query complexity. 0 means unlimited.
   */
  public int $query_complexity = 0;

  /**
   * The maximum allowed query depth. 0 means unlimited.
   */
  public int $query_depth = 0;

  /**
   * The server's endpoint.
   */
  public string $endpoint;

  /**
   * Persisted query plugins configuration.
   */
  public array $persisted_queries_settings = [];

  /**
   * Persisted query plugin instances available on this server.
   *
   * @var array|null
   */
  protected ?array $persisted_query_instances = NULL;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function executeOperation(OperationParams $operation): ExecutionResult {
    $previous = Executor::getImplementationFactory();
    Executor::setImplementationFactory([
      \Drupal::service('graphql.executor'),
      'create',
    ]);

    try {
      $config = $this->configuration();
      $result = (new Helper())->executeOperation($config, $operation);

      // In case execution fails before the execution stage, we have to wrap the
      // result object here.
      if (!($result instanceof ExecutionResult)) {
        $result = new ExecutionResult($result->data, $result->errors, $result->extensions);
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
  public function executeBatch($operations): array {
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
  public function configuration(): ServerConfig {
    $params = \Drupal::getContainer()->getParameter('graphql.config');
    /** @var \Drupal\graphql\Plugin\SchemaPluginManager $manager */
    $manager = \Drupal::service('plugin.manager.graphql.schema');
    $plugin = $manager->getInstanceFromServer($this);

    // Create the server config.
    $registry = $plugin->getResolverRegistry();
    $server = ServerConfig::create();
    $server->setDebugFlag($this->get('debug_flag'));
    $server->setQueryBatching(!!$this->get('batching'));
    $server->setValidationRules($this->getValidationRules());
    $server->setPersistedQueryLoader($this->getPersistedQueryLoader());
    $server->setContext($this->getContext($plugin, $params));
    $server->setFieldResolver($this->getFieldResolver($registry));
    $server->setSchema($plugin->getSchema());
    $server->setPromiseAdapter(new SyncPromiseAdapter());

    return $server;
  }

  /**
   * Returns to root value to use when resolving queries against the schema.
   *
   * @todo Handle this through configuration (e.g. a context value).
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
  protected function getRootValue(): mixed {
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
   *   The GraphQL module configuration.
   *
   * @return mixed|callable
   *   The context object for query execution or a callable factory.
   */
  protected function getContext(SchemaPluginInterface $schema, array $config): mixed {
    // Each document (e.g. in a batch query) gets its own resolve context. This
    // allows us to collect the cache metadata and contextual values (e.g.
    // inheritance for language) for each query separately.
    return function (OperationParams $params, DocumentNode $document, $type) use ($schema, $config) {
      $context = new ResolveContext($this, $params, $document, $type, $config);
      $context->addCacheTags(['graphql_response']);
      $context->addCacheableDependency($this);

      if ($schema instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($schema);
      }

      return $context;
    };
  }

  /**
   * Returns the default field resolver.
   *
   * @todo Handle this through configuration on the server.
   *
   * Fields that don't explicitly declare a field resolver will use this one
   * as a fallback.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   *
   * @return callable|null
   *   The default field resolver.
   */
  protected function getFieldResolver(ResolverRegistryInterface $registry): ?callable {
    return function ($value, array $args, ResolveContext $context, ResolveInfo $info) use ($registry) {
      $field = new FieldContext($context, $info);
      $result = $registry->resolveField($value, $args, $context, $info, $field);
      return DeferredUtility::applyFinally($result, function ($result) use ($field, $context): void {
        if ($result instanceof CacheableDependencyInterface) {
          $field->addCacheableDependency($result);
        }

        $context->addCacheableDependency($field);
      });
    };
  }

  /**
   * {@inheritDoc}
   */
  public function addPersistedQueryInstance(PersistedQueryPluginInterface $queryPlugin): void {
    // Make sure the persistedQueryInstances are loaded before trying to add a
    // plugin to them.
    if (is_null($this->persisted_query_instances)) {
      $this->getPersistedQueryInstances();
    }
    $this->persisted_query_instances[$queryPlugin->getPluginId()] = $queryPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function removePersistedQueryInstance($queryPluginId): void {
    // Make sure the persistedQueryInstances are loaded before trying to remove
    // a plugin from them.
    if (is_null($this->persisted_query_instances)) {
      $this->getPersistedQueryInstances();
    }
    unset($this->persisted_query_instances[$queryPluginId]);
  }

  /**
   * {@inheritDoc}
   */
  public function removeAllPersistedQueryInstances(): void {
    $this->persisted_query_instances = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistedQueryInstances(): array {
    if (!is_null($this->persisted_query_instances)) {
      return $this->persisted_query_instances;
    }
    $this->persisted_query_instances = [];

    /** @var \Drupal\graphql\Plugin\PersistedQueryPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.graphql.persisted_query');
    $definitions = $plugin_manager->getDefinitions();
    $persisted_queries_settings = $this->get('persisted_queries_settings');
    foreach ($definitions as $id => $definition) {
      if (isset($persisted_queries_settings[$id])) {
        $configuration = !empty($persisted_queries_settings[$id]) ? $persisted_queries_settings[$id] : [];
        $this->persisted_query_instances[$id] = $plugin_manager->createInstance($id, $configuration);
      }
    }
    uasort($this->persisted_query_instances, function ($a, $b) {
      return $a->getWeight() <= $b->getWeight() ? -1 : 1;
    });

    return $this->persisted_query_instances;
  }

  /**
   * Returns a callable for loading persisted queries.
   *
   * @return callable
   *   The persisted query loader.
   */
  protected function getPersistedQueryLoader(): callable {
    return function ($id, OperationParams $params) {
      $sortedPersistedQueryInstances = $this->getPersistedQueryInstances();
      if (!empty($sortedPersistedQueryInstances)) {
        foreach ($sortedPersistedQueryInstances as $persistedQueryInstance) {
          $query = $persistedQueryInstance->getQuery($id, $params);
          if (!is_null($query)) {
            return $query;
          }
        }
      }
    };
  }

  /**
   * Returns the validation rules to use for the query.
   *
   * @todo Handle this through configurable plugins on the server.
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
  protected function getValidationRules(): array|callable {
    return function (OperationParams $params, DocumentNode $document, $operation) {
      if (isset($params->queryId)) {
        // Assume that pre-parsed documents are already validated. This allows
        // us to store pre-validated query documents e.g. for persisted queries
        // effectively improving performance by skipping run-time validation.
        return [];
      }

      $rules = array_values(DocumentValidator::defaultRules());
      if ($this->getDisableIntrospection()) {
        $rules[] = new DisableIntrospection(DisableIntrospection::ENABLED);
      }
      if ($this->getQueryDepth()) {
        $rules[] = new QueryDepth($this->getQueryDepth());
      }
      if ($this->getQueryComplexity()) {
        $rules[] = new QueryComplexity($this->getQueryComplexity());
      }

      return $rules;
    };
  }

  /**
   * Gets disable introspection config.
   *
   * @return bool
   *   The disable introspection config, FALSE otherwise.
   */
  public function getDisableIntrospection(): bool {
    return $this->disable_introspection;
  }

  /**
   * Sets disable introspection config.
   *
   * @param bool $introspection
   *   The value for the disable introspection config.
   *
   * @return $this
   */
  public function setDisableIntrospection(bool $introspection) {
    $this->disable_introspection = $introspection;
    return $this;
  }

  /**
   * Gets query depth config.
   *
   * @return int
   *   The query depth.
   */
  public function getQueryDepth(): int {
    return $this->query_depth;
  }

  /**
   * Sets query depth config.
   *
   * @param int $depth
   *   The value for the query depth config.
   *
   * @return $this
   */
  public function setQueryDepth(int $depth) {
    $this->query_depth = $depth;
    return $this;
  }

  /**
   * Gets query complexity config.
   *
   * @return int
   *   The query complexity.
   */
  public function getQueryComplexity(): int {
    return $this->query_complexity;
  }

  /**
   * Sets query complexity config.
   *
   * @param int $complexity
   *   The value for the query complexity config.
   *
   * @return $this
   */
  public function setQueryComplexity(int $complexity) {
    $this->query_complexity = $complexity;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    // Write all the persisted queries configuration.
    $persistedQueryInstances = $this->getPersistedQueryInstances();
    // Reset settings array after getting instances as it might be used when
    // obtaining them. This would break a config import containing persisted
    // queries settings as it would end up empty.
    $this->persisted_queries_settings = [];
    if (!empty($persistedQueryInstances)) {
      foreach ($persistedQueryInstances as $plugin_id => $plugin) {
        $this->persisted_queries_settings[$plugin_id] = $plugin->getConfiguration();
      }
    }

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE): void {
    parent::postSave($storage, $update);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities): void {
    parent::postDelete($storage, $entities);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

}
