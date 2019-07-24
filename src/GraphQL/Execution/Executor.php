<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\Event\OperationEvent;
use Drupal\graphql\GraphQL\Execution\ExecutionResult as CacheableExecutionResult;
use Drupal\graphql\GraphQL\Utility\DocumentSerializer;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\ExecutorImplementation;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Executor\ReferenceExecutor;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Executor implements ExecutorImplementation {

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
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @var \GraphQL\Executor\Promise\PromiseAdapter
   */
  protected $adapter;

  /**
   * @var \GraphQL\Language\AST\DocumentNode
   */
  protected $document;

  /**
   * @var \Drupal\graphql\GraphQL\Execution\ResolveContext
   */
  protected $context;

  /**
   * @var mixed
   */
  protected $root;

  /**
   * @var array
   */
  protected $variables;

  /**
   * @var \GraphQL\Type\Schema
   */
  protected $schema;

  /**
   * @var string
   */
  protected $operation;

  /**
   * @var callable
   */
  protected $resolver;

  /**
   * Executor constructor.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for caching query results.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Type\Schema $schema
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param $root
   * @param $variables
   * @param $operation
   * @param $resolver
   */
  public function __construct(
    CacheContextsManager $contextsManager,
    CacheBackendInterface $cacheBackend,
    RequestStack $requestStack,
    EventDispatcherInterface $dispatcher,
    PromiseAdapter $adapter,
    Schema $schema,
    DocumentNode $document,
    ResolveContext $context,
    $root,
    $variables,
    $operation,
    $resolver
  ) {
    $this->contextsManager = $contextsManager;
    $this->cacheBackend = $cacheBackend;
    $this->requestStack = $requestStack;
    $this->dispatcher = $dispatcher;

    $this->adapter = $adapter;
    $this->document = $document;
    $this->context = $context;
    $this->root = $root;
    $this->variables = $variables;
    $this->schema = $schema;
    $this->operation = $operation;
    $this->resolver = $resolver;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   * @param \GraphQL\Type\Schema $schema
   * @param \GraphQL\Language\AST\DocumentNode $document
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param $root
   * @param $variables
   * @param $operation
   * @param $resolver
   *
   * @return \Drupal\graphql\GraphQL\Execution\Executor
   */
  public static function create(
    ContainerInterface $container,
    PromiseAdapter $adapter,
    Schema $schema,
    DocumentNode $document,
    ResolveContext $context,
    $root,
    $variables,
    $operation,
    $resolver
  ) {
    return new static(
      $container->get('cache_contexts_manager'),
      $container->get('cache.graphql.results'),
      $container->get('request_stack'),
      $container->get('event_dispatcher'),
      $adapter,
      $schema,
      $document,
      $context,
      $root,
      $variables,
      $operation,
      $resolver
    );
  }

  /**
   * {@inheritdoc}
   */
  public function doExecute(): Promise {
    $server = $this->context->getServer();
    $type = AST::getOperation($this->document, $this->operation);
    if ($type === 'query' && !!$server->get('caching')) {
      return $this->doExecuteCached($this->cachePrefix());
    }

    // This operation can never be cached because we are either in development
    // mode (caching is disabled) or this is a non-cacheable operation.
    return $this->doExecuteUncached()->then(function ($result) {
      $this->context->mergeCacheMaxAge(0);

      $result = new CacheableExecutionResult($result->data, $result->extensions, $result->errors);
      $result->addCacheableDependency($this->context);
      return $result;
    });
  }

  /**
   * @param string $prefix
   *
   * @return \GraphQL\Executor\Promise\Promise
   */
  protected function doExecuteCached($prefix) {
    if ($result = $this->cacheRead($prefix)) {
      return $this->adapter->createFulfilled($result);
    }

    return $this->doExecuteUncached()->then(function (ExecutionResult $result) use ($prefix) {
      if (!empty($result->errors)) {
        $this->context->mergeCacheMaxAge(0);
      }

      $result = new CacheableExecutionResult($result->data, $result->extensions, $result->errors);
      $result->addCacheableDependency($this->context);
      if ($result->getCacheMaxAge() !== 0) {
        $this->cacheWrite($prefix, $result);
      }

      return $result;
    });
  }

  /**
   * @return \GraphQL\Executor\Promise\Promise
   */
  protected function doExecuteUncached() {
    $executor = ReferenceExecutor::create(
      $this->adapter,
      $this->schema,
      $this->document,
      $this->root,
      $this->context,
      $this->variables,
      $this->operation,
      $this->resolver
    );

    $event = new OperationEvent($this->context);
    $this->dispatcher->dispatch(OperationEvent::GRAPHQL_OPERATION_BEFORE, $event);

    return $executor->doExecute()->then(function ($result)  {
      $event = new OperationEvent($this->context, $result);
      $this->dispatcher->dispatch(OperationEvent::GRAPHQL_OPERATION_AFTER, $event);

      return $result;
    });
  }

  /**
   * @return string
   */
  protected function cachePrefix() {
    // Sorting the variables and extensions will cause fewer cache vectors.
    // TODO: Should we try to sort these recursively?
    $variables = $this->variables ?: [];
    ksort($variables);
    // TODO: Should we submit a pull request to also pass the extensions in the executor?
    $extensions = $this->context->getOperation()->extensions ?: [];
    ksort($extensions);

    $hash = hash('sha256', serialize([
      'query' => DocumentSerializer::serializeDocument($this->document),
      'variables' => $variables,
      'extensions' => $extensions,
    ]));

    return $hash;
  }

  /**
   * @param array $contexts
   *
   * @return string
   */
  protected function cacheSuffix(array $contexts = []) {
    $keys = $this->contextsManager->convertTokensToKeys($contexts)->getKeys();
    return hash('sha256', serialize($keys));
  }

  /**
   * @param string $prefix
   *
   * @return \GraphQL\Executor\ExecutionResult|null
   */
  protected function cacheRead($prefix) {
    if (($cache = $this->cacheBackend->get("contexts:$prefix"))) {
      $suffix = $this->cacheSuffix($cache->data ?? []);
      if (($cache = $this->cacheBackend->get("result:$prefix:$suffix"))) {
        $result = new CacheableExecutionResult($cache->data['data'], [], $cache->data['extensions']);
        $result->addCacheableDependency($cache->data['metadata']);
        return $result;
      }
    }

    return NULL;
  }

  /**
   * @param string $prefix
   * @param \Drupal\graphql\GraphQL\Execution\ExecutionResult $result
   *
   * @return \Drupal\graphql\GraphQL\Execution\Executor
   */
  protected function cacheWrite($prefix, CacheableExecutionResult $result) {
    $contexts = $result->getCacheContexts();
    $expire = $this->maxAgeToExpire($result->getCacheMaxAge());
    $tags = $result->getCacheTags();
    $suffix = $this->cacheSuffix($contexts);

    $metadata = new CacheableMetadata();
    $metadata->addCacheableDependency($result);

    $cache = [
      'data' => $result->data,
      'extensions' => $result->extensions,
      'metadata' => $metadata,
    ];

    $this->cacheBackend->setMultiple([
      "contexts:$prefix"       => ['data' => $contexts, 'expire' => $expire, 'tags' => $tags],
      "result:$prefix:$suffix" => ['data' => $cache,   'expire' => $expire, 'tags' => $tags],
    ]);

    return $this;
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
