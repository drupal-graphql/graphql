<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Visitors\CacheMetadataCalculator;
use Drupal\graphql\GraphQL\Visitors\ComplexityCalculator;
use Drupal\graphql\GraphQL\Visitors\QueryEdgeCollector;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Server\Helper;
use GraphQL\Server\OperationParams;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
use GraphQL\Utils\AST;
use GraphQL\Utils\TypeInfo;
use GraphQL\Utils\Utils;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\AbstractValidationRule;
use GraphQL\Validator\ValidationContext;

class QueryProcessor {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $pluginManager;

  /**
   * The query provider service.
   *
   * @var \Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface
   */
  protected $queryProvider;

  /**
   * Processor constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface $queryProvider
   *   The query provider service.
   */
  public function __construct(
    AccountProxyInterface $currentUser,
    SchemaPluginManager $pluginManager,
    QueryProviderInterface $queryProvider
  ) {
    $this->currentUser = $currentUser;
    $this->pluginManager = $pluginManager;
    $this->queryProvider = $queryProvider;
  }

  /**
   * Processes one or multiple graphql operations.
   *
   * @param string $schema
   *   The plugin id of the schema to use.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $params
   *   The graphql operation(s) to execute.
   * @param array $globals
   *   The query context.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult|\Drupal\graphql\GraphQL\Execution\QueryResult[]
   *   The query result.
   *
   */
  public function processQuery($schema, $params, array $globals = []) {
    // Load the plugin from the schema manager.
    $plugin = $this->pluginManager->createInstance($schema);
    $schema = $plugin->getSchema();

    // If the current user has appropriate permissions, allow to bypass
    // the secure fields restriction.
    $globals['bypass field security'] = $this->currentUser->hasPermission('bypass graphql field security');

    // Create the server config.
    $config = ServerConfig::create();
    $config->setDebug(!empty($globals['development']));
    $config->setSchema($schema);
    $config->setQueryBatching(TRUE);
    $config->setContext(function () use ($globals) {
      // Each document (e.g. in a batch query) gets its own resolve context but
      // the global parameters are shared. This allows us to collect the cache
      // metadata and contextual values (e.g. inheritance for language) for each
      // query separately.
      return new ResolveContext($globals);
    });

    $config->setValidationRules(function (OperationParams $params, DocumentNode $document, $operation) {
      if (!isset($params->queryId)) {
        // Assume that pre-parsed documents are already validated. This allows
        // us to store pre-validated query documents e.g. for persisted queries
        // effectively improving performance by skipping run-time validation.
        return [];
      }

      return DocumentValidator::allRules();
    });

    $config->setPersistentQueryLoader(function ($id, OperationParams $params) {
      if ($query = $this->queryProvider->getQuery($id, $params)) {
        return $query;
      }

      throw new RequestError(sprintf("Failed to load query map for id '%s'.", $id));
    });

    if (is_array($params)) {
      return $this->executeBatch($config, $params);
    }

    return $this->executeSingle($config, $params);
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   *
   * @return mixed
   */
  public function executeSingle(ServerConfig $config, OperationParams $params) {
    $adapter = new SyncPromiseAdapter();
    $result = $this->executeOperation($adapter, $config, $params, FALSE);
    return $adapter->wait($result);
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param array $params
   *
   * @return mixed
   */
  public function executeBatch(ServerConfig $config, array $params) {
    $adapter = new SyncPromiseAdapter();
    $result = array_map(function ($params) use ($adapter, $config) {
      return $this->executeOperation($adapter, $config, $params, TRUE);
    }, $params);

    $result = $adapter->all($result);
    return $adapter->wait($result);
  }

  /**
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param bool $batching
   *
   * @return \GraphQL\Executor\Promise\Promise
   */
  protected function executeOperation(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, $batching = FALSE) {
    try {
      if (!$config->getSchema()) {
        throw new \LogicException('Missing schema for query execution.');
      }

      if ($batching && !$config->getQueryBatching()) {
        throw new RequestError('Batched queries are not supported by this server.');
      }

      if ($errors = (new Helper())->validateOperationParams($params)) {
        $errors = Utils::map($errors, function (RequestError $err) {
          return Error::createLocatedError($err, NULL, NULL);
        });

        return $adapter->createFulfilled(new QueryResult(NULL, $errors));
      }

      $schema = $config->getSchema();
      $variables = $params->variables;
      $operation = $params->operation;
      $document = $params->queryId ? $this->loadPersistedQuery($config, $params) : $params->query;
      if (!$document instanceof DocumentNode) {
        $document = Parser::parse($document);
      }

      // Read the operation type from the document. Subscriptions and mutations
      // only work through POST requests. One cannot have mutations and queries
      // in the same document, hence this check is sufficient.
      $type = AST::getOperation($document, $operation);
      if ($params->isReadOnly() && $type !== 'query') {
        throw new RequestError('GET requests are only supported for query operations.');
      }

      $context = $this->resolveContextValue($config, $params, $document, $operation);
      $complexity = $this->resolveAllowedComplexity($config, $params, $document, $operation);
      $rules = $this->resolveValidationRules($config, $params, $document, $operation);
      $info = new TypeInfo($schema);
      $validation = new ValidationContext($schema, $document, $info);

      // Add a special visitor to the set of validation rules which will collect
      // all nodes and fields (all possible edges) from the document to allow
      // for static query analysis (e.g. for calculating the query complexity or
      // for determining the cache metadata of a query so we can perform cache
      // lookups).
      $visitors = array_map(function (AbstractValidationRule $rule) use ($validation, $context) {
        return $rule->getVisitor($validation);
      }, array_merge($rules, [new QueryEdgeCollector(array_filter([
        // Query operations can be cached. Collect static cache metadata from
        // the document during the visitor phase.
        $type === 'query' ? new CacheMetadataCalculator($context) : NULL,
        $complexity !== NULL ? new ComplexityCalculator($complexity) : NULL,
      ]))]));

      // Run the query visitor with the prepared validation rules and the cache
      // metadata collector and query complexity calculator.
      Visitor::visit($document, Visitor::visitWithTypeInfo($info, Visitor::visitInParallel($visitors)));

      // If one of the validation rules found any problems, do not resolve the
      // query and bail out early instead.
      if ($errors = $validation->getErrors()) {
        return $adapter->createFulfilled(new QueryResult(NULL, $errors));
      }

      // TODO: Perform a cach lookup.

      $resolver = $config->getFieldResolver();
      $root = $this->resolveRootValue($config, $params, $document, $operation);
      $promise = Executor::promiseToExecute(
        $adapter,
        $schema,
        $document,
        $root,
        $context,
        $variables,
        $operation,
        $resolver
      );

      return $promise->then(function (ExecutionResult $result) use ($context) {
        $metadata = (new CacheableMetadata())->addCacheableDependency($context);
        return new QueryResult($result->data, $result->errors, $result->extensions, $metadata);
      });
    }
    catch (RequestError $exception) {
      $result = $adapter->createFulfilled(new QueryResult(NULL, [Error::createLocatedError($exception)]));
    }
    catch (Error $exception) {
      $result = $adapter->createFulfilled(new QueryResult(NULL, [$exception]));
    }

    return $result->then(function(QueryResult $result) use ($config) {
      if ($config->getErrorsHandler()) {
        $result->setErrorsHandler($config->getErrorsHandler());
      }

      if ($config->getErrorFormatter() || $config->getDebug()) {
        $result->setErrorFormatter(FormattedError::prepareFormatter($config->getErrorFormatter(), $config->getDebug()));
      }

      return $result;
    });
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param $operation
   *
   * @return mixed
   */
  protected function resolveRootValue(ServerConfig $config, OperationParams $params, DocumentNode $document, $operation) {
    $root = $config->getRootValue();
    if (is_callable($root)) {
      $root = $root($params, $document, $operation);
    }

    return $root;
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param $operation
   *
   * @return mixed
   */
  protected function resolveContextValue(ServerConfig $config, OperationParams $params, DocumentNode $document, $operation) {
    $context = $config->getContext();
    if (is_callable($context)) {
      $context = $context($params, $document, $operation);
    }

    return $context;
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param $operation
   *
   * @return array
   */
  protected function resolveValidationRules(ServerConfig $config, OperationParams $params, DocumentNode $document, $operation) {
    // Allow customizing validation rules per operation:
    $rules = $config->getValidationRules();
    if (is_callable($rules)) {
      $rules = $rules($params, $document, $operation);
      if (!is_array($rules)) {
        throw new \LogicException(sprintf("Expecting validation rules to be array or callable returning array, but got: %s", Utils::printSafe($rules)));
      }
    }

    return $rules;
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param $operation
   *
   * @return int|null
   */
  protected function resolveAllowedComplexity(ServerConfig $config, OperationParams $params, DocumentNode $document, $operation) {
    return NULL;
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   *
   * @return mixed
   * @throws \GraphQL\Server\RequestError
   */
  protected function loadPersistedQuery(ServerConfig $config, OperationParams $params) {
    if (!$loader = $config->getPersistentQueryLoader()) {
      throw new RequestError('Persisted queries are not supported by this server.');
    }

    $source = $loader($params->queryId, $params);
    if (!is_string($source) && !$source instanceof DocumentNode) {
      throw new \LogicException(sprintf('The persisted query loader must return query string or instance of %s but got: %s.', DocumentNode::class, Utils::printSafe($source)));
    }

    return $source;
  }

}
