<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PathProcessor\PathProcessorManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request policy for allowing GraphQL queries to be cached.
 */
class DenyUnsafeMethodUnlessQuery implements RequestPolicyInterface {

  /**
   * A path processor manager.
   *
   * @var \Drupal\Core\PathProcessor\PathProcessorManager
   */
  protected $pathProcessor;

  public function __construct(PathProcessorManager $pathProcessor) {
    $this->pathProcessor = $pathProcessor;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!$request->isMethodSafe() && !($request->getMethod() === 'POST' && $this->pathProcessor->processInbound($request->getPathInfo(), $request) === '/graphql')) {
      return static::DENY;
    }

    return NULL;
  }

}
