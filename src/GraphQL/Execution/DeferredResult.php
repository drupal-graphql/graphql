<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Youshido\GraphQL\Execution\DeferredResolverInterface;
use Youshido\GraphQL\Execution\DeferredResult as OriginalDeferredResult;

/**
 * Extension of deferred result with cache handling.
 */
class DeferredResult extends OriginalDeferredResult {

  /**
   * The metadata bag.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $metadata;

  /**
   * The original callback.
   *
   * @var callable
   */
  protected $originalCallback;

  /**
   * DeferredResult constructor.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   *   The metadata bag.
   * @param \Youshido\GraphQL\Execution\DeferredResolverInterface $resolver
   *   The deferred resolver.
   * @param callable $callback
   *   The callback to be applied after resolving.
   */
  public function __construct(CacheableMetadata $metadata, DeferredResolverInterface $resolver, callable $callback) {
    $this->metadata = $metadata;
    $this->originalCallback = $callback;
    parent::__construct($resolver, function($result) {
      if ($result instanceof CacheableDependencyInterface) {
        $this->metadata->addCacheableDependency($this);
      }

      if ($result instanceof CacheableValue) {
        $result = $result->getValue();
      }

      return call_user_func($this->originalCallback, $result);
    });
  }

}
