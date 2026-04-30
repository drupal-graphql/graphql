<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service to make our GraphQL executor, can be swapped out.
 */
class ExecutorFactory {

  /**
   * ExecutorFactory constructor.
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
   */
  public function __construct(
    protected CacheContextsManager $contextsManager,
    protected CacheBackendInterface $cacheBackend,
    protected TimeInterface $time,
    protected EventDispatcherInterface $dispatcher,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {
  }

  /**
   * Factory method to make a new executor.
   */
  public function create(
    PromiseAdapter $adapter,
    Schema $schema,
    DocumentNode $document,
    mixed $root,
    ResolveContext $context,
    array $variables,
    ?string $operation,
    callable $resolver,
  ): Executor {
    return new Executor(
      $this->contextsManager,
      $this->cacheBackend,
      $this->time,
      $this->dispatcher,
      $this->loggerFactory,
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

}
