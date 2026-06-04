<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error as ErrorUtil;
use Drupal\graphql\Event\OperationEvent;
use Drupal\graphql\GraphQL\Execution\ExecutionResult as CacheableExecutionResult;
use Drupal\graphql\GraphQL\Utility\DocumentSerializer;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\ExecutorImplementation;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Executor\ReferenceExecutor;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Executes GraphQL queries with cache lookup.
 */
class Executor implements ExecutorImplementation {

  /**
   * Create a new GraphQL request Executor.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for caching query results.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The date/time service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   *   The adapter for promises.
   * @param \GraphQL\Type\Schema $schema
   *   The parsed GraphQL schema.
   * @param \GraphQL\Language\AST\DocumentNode $document
   *   Represents the GraphQL schema document.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context to pass down during field resolving.
   * @param mixed $root
   *   The root of the GraphQL execution tree.
   * @param array $variables
   *   Variables.
   * @param string|null $operation
   *   The operation to be performed.
   * @param callable $resolver
   *   The resolver to get results for the query.
   */
  public function __construct(
    protected CacheContextsManager $contextsManager,
    protected CacheBackendInterface $cacheBackend,
    protected TimeInterface $time,
    protected EventDispatcherInterface $dispatcher,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected PromiseAdapter $adapter,
    protected Schema $schema,
    protected DocumentNode $document,
    protected ResolveContext $context,
    protected mixed $root,
    protected array $variables,
    protected ?string $operation,
    protected $resolver,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function doExecute(): Promise {
    $server = $this->context->getServer();
    $operation_def = AST::getOperationAST($this->document, $this->operation);
    if ($operation_def && $operation_def->operation === 'query' && !!$server->get('caching')) {
      return $this->doExecuteCached($this->cachePrefix());
    }

    // This operation can never be cached because we are either in development
    // mode (caching is disabled) or this is a non-cacheable operation.
    return $this->doExecuteUncached()->then(function ($result) {
      $this->context->mergeCacheMaxAge(0);

      $result = new CacheableExecutionResult($result->data, $result->errors, $result->extensions);
      $result->addCacheableDependency($this->context);
      return $result;
    });
  }

  /**
   * Try to return cached results, otherwise resolve the query.
   */
  protected function doExecuteCached(string $prefix): Promise {
    if ($result = $this->cacheRead($prefix)) {
      $event = new OperationEvent($this->context, $result);
      $this->dispatcher->dispatch($event, OperationEvent::GRAPHQL_OPERATION_CACHE_HIT);
      return $this->adapter->createFulfilled($result);
    }

    return $this->doExecuteUncached()->then(function (ExecutionResult $result) use ($prefix) {
      if (!empty($result->errors)) {
        $this->context->mergeCacheMaxAge(0);
      }

      $result = new CacheableExecutionResult($result->data, $result->errors, $result->extensions);
      $result->addCacheableDependency($this->context);
      if ($result->getCacheMaxAge() !== 0) {
        $this->cacheWrite($prefix, $result);
      }

      return $result;
    });
  }

  /**
   * Get query results on a cache miss.
   */
  protected function doExecuteUncached(): Promise {
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
    $this->dispatcher->dispatch($event, OperationEvent::GRAPHQL_OPERATION_BEFORE);

    return $executor->doExecute()->then(function ($result) {
      $event = new OperationEvent($this->context, $result);
      $this->dispatcher->dispatch($event, OperationEvent::GRAPHQL_OPERATION_AFTER);

      $this->logUnsafeErrors($this->context->getOperation(), $result);

      return $result;
    });
  }

  /**
   * Logs internal unsafe errors if there are any (not shown to clients).
   */
  protected function logUnsafeErrors(OperationParams $operation, ExecutionResult $result): void {
    $hasUnsafeErrors = FALSE;
    $previousErrors = [];

    foreach ($result->errors as $index => $error) {
      // Don't log errors intended for clients, only log those that
      // a client would not be able to solve, they'd require work from
      // a server developer.
      if ($error->isClientSafe()) {
        continue;
      }

      $hasUnsafeErrors = TRUE;
      // Log the error that cause the error we caught. This makes the error
      // logs more useful because GraphQL usually wraps the original error.
      if ($error->getPrevious() instanceof \Throwable) {
        $previousErrors[] = strtr(
          "For error #@index: %type: @message in %function (line %line of %file)\n@backtrace_string.",
          ErrorUtil::decodeException($error->getPrevious()) + ['@index' => $index]
        );
      }
    }

    if ($hasUnsafeErrors) {
      $this->loggerFactory->get('graphql')->error(
        "There were errors during a GraphQL execution.\nOperation details:\n<pre>\n{details}\n</pre>\nPrevious errors:\n<pre>\n{previous}\n</pre>",
        [
          'details' => json_encode([
            '$operation' => $operation,
            // Do not pass $result to json_encode because it implements
            // JsonSerializable and strips some data out during the
            // serialization.
            '$result->data' => $result->data,
            '$result->errors' => array_map(function ($error) {
              return (string) $error;
            }, $result->errors),
            '$result->extensions' => $result->extensions,
          ], JSON_PRETTY_PRINT),
          'previous' => implode('\n\n', $previousErrors),
        ]
      );
    }
  }

  /**
   * Calculates the cache prefix from context for the current query.
   */
  protected function cachePrefix(): string {
    // Sorting the variables and extensions will cause fewer cache vectors.
    // @todo Should we try to sort these recursively?
    $variables = $this->variables ?: [];
    ksort($variables);
    // @todo Should we submit a pull request to also pass the extensions in the
    // executor?
    $extensions = $this->context->getOperation()->extensions ?: [];
    ksort($extensions);

    $hash = hash('sha256', serialize([
      'query' => DocumentSerializer::serializeDocument($this->document),
      'variables' => $variables,
      'extensions' => $extensions,
      'operation' => $this->operation,
      'server_id' => $this->context->getServer()->id(),
    ]));

    return $hash;
  }

  /**
   * Calculate the cache suffix for the current contexts.
   */
  protected function cacheSuffix(array $contexts = []): string {
    $keys = $this->contextsManager->convertTokensToKeys($contexts)->getKeys();
    return hash('sha256', serialize($keys));
  }

  /**
   * Lookup cached results by contexts for this query.
   */
  protected function cacheRead(string $prefix): ?ExecutionResult {
    if (($cache = $this->cacheBackend->get("contexts:$prefix"))) {
      $suffix = $this->cacheSuffix($cache->data ?? []);
      if (($cache = $this->cacheBackend->get("result:$prefix:$suffix"))) {
        $result = new CacheableExecutionResult($cache->data['data'], [], $cache->data['extensions']);
        $result->addCacheableDependency($cache->data['metadata']);
        // The metadata cacheMaxAge is valid at the time the cache entry is
        // created. However, it may have been in the cache for a while, which
        // means it's outdated. The expire timestamp matches the maxAge at
        // the time of insertion, which can be used to calculate the fresh
        // max-age of the response.
        $result->mergeCacheMaxAge((int) $cache->expire === Cache::PERMANENT ? Cache::PERMANENT : $cache->expire - $this->time->getCurrentTime());
        return $result;
      }
    }

    return NULL;
  }

  /**
   * Store results in cache.
   */
  protected function cacheWrite(string $prefix, CacheableExecutionResult $result): static {
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
      "contexts:$prefix"       => [
        'data' => $contexts,
        'expire' => $expire,
        'tags' => $tags,
      ],
      "result:$prefix:$suffix" => [
        'data' => $cache,
        'expire' => $expire,
        'tags' => $tags,
      ],
    ]);

    return $this;
  }

  /**
   * Maps a cache max age value to an "expire" value for the Cache API.
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire(int $maxAge): int {
    $time = $this->time->getRequestTime();
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $time + $maxAge;
  }

}
