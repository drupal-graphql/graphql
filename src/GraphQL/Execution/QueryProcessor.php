<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Execution\Visitor\CacheMetadataVisitor;
use Drupal\graphql\GraphQL\Execution\Visitor\MaxComplexityVisitor;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Symfony\Component\HttpFoundation\RequestStack;
use Youshido\GraphQL\Exception\ResolveException;

class QueryProcessor {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The schema loader service.
   *
   * @var \Drupal\graphql\GraphQL\Schema\SchemaLoader
   */
  protected $schemaLoader;

  /**
   * The cache contexts manager service.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $contextsManager;

  /**
   * The cache backend for caching responses.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * QueryProcessor constructor.
   *
   * @param \Drupal\graphql\GraphQL\Schema\SchemaLoader $schemaLoader
   *   The schema loader service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for caching response.
   */
  public function __construct(
    SchemaLoader $schemaLoader,
    AccountProxyInterface $currentUser,
    RequestStack $requestStack,
    CacheBackendInterface $cacheBackend,
    CacheContextsManager $contextsManager
  ) {
    $this->currentUser = $currentUser;
    $this->schemaLoader = $schemaLoader;
    $this->requestStack = $requestStack;
    $this->cacheBackend = $cacheBackend;
    $this->contextsManager = $contextsManager;
  }

  /**
   * Processes a graphql query.
   *
   * @param string $id
   *   The name of the schema to process the query against.
   * @param string $query
   *   The GraphQL query.
   * @param array $variables
   *   The query variables.
   * @param bool $useCache
   *   Whether to use caching.
   * @param int|null $maxComplexity
   *   The maximum complexity of the query or NULL if any complexity is allowed.
   * @param bool $bypassSecurity
   *   Bypass field security
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult .
   *   The query result.
   */
  public function processQuery($id, $query, array $variables = [], $useCache = TRUE, $maxComplexity = NULL, $bypassSecurity = FALSE) {
    if (!$schema = $this->schemaLoader->getSchema($id)) {
      throw new \InvalidArgumentException(sprintf('Could not load schema %s', [$id]));
    }

    // The processor isolates the parsing and execution of the query.
    $processor = new Processor($schema, $query, $variables);
    $context = $processor->getExecutionContext();
    $container = $context->getContainer();

    // Set up some parameters in the container.
    $secure = $bypassSecurity || $this->currentUser->hasPermission('bypass graphql field security');
    $metadata = new CacheableMetadata();
    $container->set('secure', $secure);
    $container->set('metadata', $metadata);

    $visitor = new CacheMetadataVisitor();
    /** @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata */
    $metadata = $processor->reduceRequest($visitor, function ($result) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheableDependency($result);
      // TODO: Use a cache context that includes the AST instead of the raw values.
      $metadata->addCacheContexts(['gql']);
      $metadata->addCacheTags(['graphql_response']);

      return $metadata;
    }) ?: new CacheableMetadata();

    // The cache identifier will later be re-used for writing the cache entry.
    $cid = $this->getCacheIdentifier($metadata);
    if (!empty($useCache) && $metadata->getCacheMaxAge() !== 0) {
      if (($cache = $this->cacheBackend->get($cid)) && $result = $cache->data) {
        return $result;
      }
    }

    if (!empty($maxComplexity)) {
      $visitor = new MaxComplexityVisitor();
      $processor->reduceRequest($visitor, function ($result) use ($maxComplexity) {
        if ($result > $maxComplexity) {
          throw new ResolveException('Maximum complexity exceeded.');
        }
      });
    }

    // Retrieve the result from the processor.
    $data = $processor->resolveRequest();

    // Add collected cache metadata from the query processor.
    if ($container->has('metadata') && ($collected = $container->get('metadata'))) {
      if ($collected instanceof CacheableDependencyInterface) {
        $metadata->addCacheableDependency($collected);
      }
    }

    // If we encountered any errors, do not cache anything.
    if ($context->hasErrors()) {
      $metadata->setCacheMaxAge(0);
    }

    // Build the result object. The keys are only set if they contain data.
    $result = new QueryResult(array_filter([
      'data' => $data,
      'errors' => $processor->getExecutionContext()->getErrorsArray(),
    ]), $metadata);

    // Write the query result into the cache if the cache metadata permits.
    if (!empty($useCache) && $metadata->getCacheMaxAge() !== 0) {
      $tags = $metadata->getCacheTags();
      $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());

      // The cache identifier for the cache entry is built based on the
      // previously extracted cache contexts from the query visitor. That
      // means, that dynamically returned cache contexts have no effect.
      $this->cacheBackend->set($cid, $result, $expire, $tags);
    }

    return $result;
  }

  /**
   * Maps a max age value to an "expire" value for the Cache API.
   *
   * @param int $maxAge
   *   A max age value.
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire($maxAge) {
    if ($maxAge === Cache::PERMANENT) {
      return Cache::PERMANENT;
    }

    return (int) $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME') + $maxAge;
  }

  /**
   * Generates a cache identifier for the passed cache contexts.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   Optional array of cache context tokens.
   *
   * @return string The generated cache identifier.
   *   The generated cache identifier.
   */
  protected function getCacheIdentifier(CacheableDependencyInterface $metadata) {
    $tokens = $metadata->getCacheContexts();
    $keys = $this->contextsManager->convertTokensToKeys($tokens)->getKeys();
    return implode(':', array_merge(['graphql'], array_values($keys)));
  }

}
