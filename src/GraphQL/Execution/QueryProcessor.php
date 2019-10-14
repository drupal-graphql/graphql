<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Drupal\graphql\GraphQL\Cache\CacheableRequestError;
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
use GraphQL\Validator\Rules\AbstractValidationRule;
use GraphQL\Validator\ValidationContext;
use GraphQL\Validator\Rules\QueryComplexity;
use Symfony\Component\HttpFoundation\RequestStack;

// TODO: Refactor this and clean it up.
class QueryProcessor {

  /**
   * The schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $pluginManager;

  /**
   * The cache backend for caching query results.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The cache contexts manager service.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $contextsManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Processor constructor.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for caching query results.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    CacheContextsManager $contextsManager,
    SchemaPluginManager $pluginManager,
    CacheBackendInterface $cacheBackend,
    RequestStack $requestStack
  ) {
    $this->contextsManager = $contextsManager;
    $this->pluginManager = $pluginManager;
    $this->cacheBackend = $cacheBackend;
    $this->requestStack = $requestStack;
  }

  /**
   * Processes one or multiple graphql operations.
   *
   * @param string $schema
   *   The plugin id of the schema to use.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $params
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult|\Drupal\graphql\GraphQL\Execution\QueryResult[]
   *   The query result.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function processQuery($schema, $params) {
    // Load the plugin from the schema manager.
    $plugin = $this->pluginManager->createInstance($schema);
    $config = $plugin->getServer();

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
    $result = $this->executeOperationWithReporting($adapter, $config, $params, FALSE);
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
      return $this->executeOperationWithReporting($adapter, $config, $params, TRUE);
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
  protected function executeOperationWithReporting(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, $batching = FALSE) {
    $result = $this->executeOperation($adapter, $config, $params, $batching);

    // Format and print errors.
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
        throw new Error('Missing schema for query execution.');
      }

      if ($batching && !$config->getQueryBatching()) {
        throw new RequestError('Batched queries are not supported by this server.');
      }

      if ($errors = $this->validateOperationParams($params)) {
        return $adapter->createFulfilled(new QueryResult(NULL, $errors));
      }

      $persisted = isset($params->queryId);
      $document = $persisted ? $this->loadPersistedQuery($config, $params) : $params->query;
      if (!$document instanceof DocumentNode) {
        $document = Parser::parse($document);
      }

      // Read the operation type from the document. Subscriptions and mutations
      // only work through POST requests. One cannot have mutations and queries
      // in the same document, hence this check is sufficient.
      $operation = $params->operation;
      $type = AST::getOperation($document, $operation);
      if ($params->isReadOnly() && $type !== 'query') {
        throw new RequestError('GET requests are only supported for query operations.');
      }

      // Only queries can be cached (mutations and subscriptions can't).
      if ($type === 'query') {
        return $this->executeCacheableOperation($adapter, $config, $params, $document, !$persisted);
      }

      return $this->executeUncachableOperation($adapter, $config, $params, $document, !$persisted);
    }
    catch (CacheableRequestError $exception) {
      return $adapter->createFulfilled(
        new QueryResult(NULL, [Error::createLocatedError($exception)], [], $exception)
      );
    }
    catch (RequestError $exception) {
      return $adapter->createFulfilled(new QueryResult(NULL, [Error::createLocatedError($exception)]));
    }
    catch (Error $exception) {
      return $adapter->createFulfilled(new QueryResult(NULL, [$exception]));
    }
  }

  /**
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param bool $validate
   *
   * @return \GraphQL\Executor\Promise\Promise|mixed
   */
  protected function executeCacheableOperation(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, DocumentNode $document, $validate = TRUE) {
    $contextCacheId = 'ccid:' . $this->cacheIdentifier($params, $document);
    if (!$config->getDebug() && $contextCache = $this->cacheBackend->get($contextCacheId)) {
      $contexts = $contextCache->data ?: [];
      $cid = 'cid:' . $this->cacheIdentifier($params, $document, $contexts);
      if ($cache = $this->cacheBackend->get($cid)) {
        return $adapter->createFulfilled($cache->data);
      }
    }

    $result = $this->doExecuteOperation($adapter, $config, $params, $document, $validate);
    return $result->then(function (QueryResult $result) use ($contextCacheId, $params, $document) {
      // Write this query into the cache if it is cacheable.
      if ($result->getCacheMaxAge() !== 0) {
        $contexts = $result->getCacheContexts();
        $expire = $this->maxAgeToExpire($result->getCacheMaxAge());
        $tags = $result->getCacheTags();
        $cid = 'cid:' . $this->cacheIdentifier($params, $document, $contexts);
        $this->cacheBackend->set($contextCacheId, $contexts, $expire, $tags);
        $this->cacheBackend->set($cid, $result, $expire, $tags);
      }

      return $result;
    });
  }

  /**
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param bool $validate
   *
   * @return \GraphQL\Executor\Promise\Promise
   */
  protected function executeUncachableOperation(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, DocumentNode $document, $validate = TRUE) {
    $result = $this->doExecuteOperation($adapter, $config, $params, $document, $validate);
    return $result->then(function (QueryResult $result) {
      // Mark the query result as uncacheable.
      $result->mergeCacheMaxAge(0);
      return $result;
    });
  }

  /**
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param bool $validate
   *
   * @return \GraphQL\Executor\Promise\Promise
   */
  protected function doExecuteOperation(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, DocumentNode $document, $validate = TRUE) {
    // If one of the validation rules found any problems, do not resolve the
    // query and bail out early instead.
    if ($validate && $errors = $this->validateOperation($config, $params, $document)) {
      return $adapter->createFulfilled(new QueryResult(NULL, $errors));
    }

    $operation = $params->operation;
    $variables = $params->variables;
    $context = $this->resolveContextValue($config, $params, $document, $operation);
    $root = $this->resolveRootValue($config, $params, $document, $operation);
    $resolver = $config->getFieldResolver();
    $schema = $config->getSchema();

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
      $metadata = (new CacheableMetadata())
        ->addCacheContexts($context->getCacheContexts())
        ->addCacheTags($context->getCacheTags())
        ->setCacheMaxAge($context->getCacheMaxAge());

      // Do not cache in development mode or if there are any errors.
      if ($context->getGlobal('development') || !empty($result->errors)) {
        $metadata->setCacheMaxAge(0);
      }

      return new QueryResult($result->data, $result->errors, $result->extensions, $metadata);
    });
  }

  /**
   * @param \GraphQL\Server\OperationParams $params
   *
   * @return array
   */
  protected function validateOperationParams(OperationParams $params) {
    $errors = (new Helper())->validateOperationParams($params);
    return array_map(function (RequestError $error) {
      return Error::createLocatedError($error, NULL, NULL);
    }, $errors);
  }

  /**
   * @param \GraphQL\Server\ServerConfig $config
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   *
   * @return \GraphQL\Error\Error[]
   * @throws \Exception
   */
  protected function validateOperation(ServerConfig $config, OperationParams $params, DocumentNode $document) {
    $operation = $params->operation;
    // Skip validation if there are no validation rules to be applied.
    if (!$rules = $this->resolveValidationRules($config, $params, $document, $operation)) {
      return [];
    }

    $schema = $config->getSchema();
    $info = new TypeInfo($schema);
    $validation = new ValidationContext($schema, $document, $info);
    $visitors = array_values(array_map(function (AbstractValidationRule $rule) use ($validation, $params) {
      // Set current variable values for QueryComplexity validation rule case
      // @see \GraphQL\GraphQL::promiseToExecute for equivalent
      if ($rule instanceof QueryComplexity && !empty($params->variables)) {
        $rule->setRawVariableValues($params->variables);
      }
      return $rule($validation);
    }, $rules));

    // Run the query visitor with the prepared validation rules and the cache
    // metadata collector and query complexity calculator.
    Visitor::visit($document, Visitor::visitWithTypeInfo($info, Visitor::visitInParallel($visitors)));

    // Return any possible errors collected during validation.
    return $validation->getErrors();
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
   * @throws \GraphQL\Server\RequestError
   */
  protected function resolveValidationRules(ServerConfig $config, OperationParams $params, DocumentNode $document, $operation) {
    // Allow customizing validation rules per operation:
    $rules = $config->getValidationRules();
    if (is_callable($rules)) {
      $rules = $rules($params, $document, $operation);
      if (!is_array($rules)) {
        throw new RequestError(sprintf("Expecting validation rules to be array or callable returning array, but got: %s", Utils::printSafe($rules)));
      }
    }

    return $rules;
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
      throw new RequestError(sprintf('The persisted query loader must return query string or instance of %s but got: %s.', DocumentNode::class, Utils::printSafe($source)));
    }

    return $source;
  }

  /**
   * @param \GraphQL\Language\AST\DocumentNode $document
   *
   * @return array
   */
  protected function serializeDocument(DocumentNode $document) {
    return $this->sanitizeRecursive(AST::toArray($document));
  }

  /**
   * @param array $item
   *
   * @return array
   */
  protected function sanitizeRecursive(array $item) {
    unset($item['loc']);

    foreach ($item as &$value) {
      if (is_array($value)) {
        $value = $this->sanitizeRecursive($value);
      }
    }

    return $item;
  }

  /**
   * @param \GraphQL\Server\OperationParams $params
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param array $contexts
   *
   * @return string
   */
  protected function cacheIdentifier(OperationParams $params, DocumentNode $document, array $contexts = []) {
    // Ignore language contexts since they are handled by graphql internally.
    $contexts = $contexts;
    $keys = $this->contextsManager->convertTokensToKeys($contexts)->getKeys();

    // Sorting the variables will cause fewer cache vectors.
    $variables = $params->variables ?: [];
    ksort($variables);

    // Prepend the hash of the serialized document to the cache contexts.
    $hash = hash('sha256', json_encode([
      'query' => $this->serializeDocument($document),
      'variables' => $variables,
    ]));

    return implode(':', array_values(array_merge([$hash], $keys)));
  }

  /**
   * Filter unused contexts.
   *
   * Removes the language contexts from a list of context ids.
   *
   * @param string[] $contexts
   *   The list of context id's.
   *
   * @return string[]
   *   The filtered list of context id's.
   */
  protected function filterCacheContexts(array $contexts) {
    return array_filter($contexts, function ($context) {
      return strpos($context, 'languages:') !== 0;
    });
  }

  /**
   * Maps a cache max age value to an "expire" value for the Cache API.
   *
   * @param int $maxAge
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire($maxAge) {
    $time = $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME');
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $time + $maxAge;
  }
}
