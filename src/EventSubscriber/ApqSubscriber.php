<?php

declare(strict_types=1);

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\graphql\Event\OperationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Save persisted queries to cache.
 */
class ApqSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a ApqSubscriber object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache to store persisted queries.
   */
  public function __construct(
    protected CacheBackendInterface $cache,
  ) {
  }

  /**
   * Handle operation start events.
   *
   * @param \Drupal\graphql\Event\OperationEvent $event
   *   The kernel event object.
   *
   * @throws \GraphQL\Error\Error
   */
  public function onBeforeOperation(OperationEvent $event): void {
    if (!array_key_exists('automatic_persisted_query', $event->getContext()->getServer()->getPersistedQueryInstances())) {
      return;
    }
    // We only need to set cache tags here, the rest is done in
    // AutomaticPersistedQuery.
    $queryHash = $event->getContext()->getOperation()->extensions['persistedQuery']['sha256Hash'] ?? '';

    if (is_string($queryHash) && $queryHash !== '') {
      // Add cache context for dynamic page cache compatibility on all
      // operations that have the hash set.
      $event->getContext()->addCacheContexts(
        ['url.query_args:variables', 'url.query_args:extensions']
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      OperationEvent::GRAPHQL_OPERATION_BEFORE => 'onBeforeOperation',
    ];
  }

}
