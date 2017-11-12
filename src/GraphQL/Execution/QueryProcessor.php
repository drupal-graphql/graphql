<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Reducers\ReducerManager;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;

/**
 * Drupal service for executing GraphQL queries.
 */
class QueryProcessor {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

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
   * The graphql container parameters.
   *
   * @var array
   */
  protected $parameters;

  /**
   * QueryProcessor constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\GraphQL\Schema\SchemaLoader $schemaLoader
   *   The schema loader service.
   * @param array $parameters
   *   The graphql container parameters.
   */
  public function __construct(
    ContainerInterface $container,
    SchemaLoader $schemaLoader,
    AccountProxyInterface $currentUser,
    array $parameters
  ) {
    $this->container = $container;
    $this->currentUser = $currentUser;
    $this->parameters = $parameters;
    $this->schemaLoader = $schemaLoader;
  }

  /**
   * Execute a GraphQL query.
   *
   * @param string $schemaId
   *   The name of the schema to process the query against.
   * @param string $query
   *   The GraphQL query.
   * @param array $variables
   *   The query variables.
   * @param bool $bypassSecurity
   *   Bypass field security
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult.
   *   The GraphQL query result.
   */
  public function processQuery($schemaId, $query, array $variables = [], $bypassSecurity = FALSE) {
    if (!$schema = $this->schemaLoader->getSchema($schemaId)) {
      throw new \InvalidArgumentException(sprintf('Could not load schema %s', [$schemaId]));
    }

    /** @var \Youshido\GraphQL\Schema\AbstractSchema $schema */
    $secure = !!($bypassSecurity || $this->currentUser->hasPermission('bypass graphql field security') || $this->parameters['development']);
    $processor = new Processor($this->container, $schema, $secure);
    $processor->processPayload($query, $variables);

    // Add collected cache metadata from the query processor.
    $metadata = new CacheableMetadata();
    $context = $processor->getExecutionContext();
    $container = $context->getContainer();
    if ($container->has('metadata')) {
      $metadata->addCacheableDependency($container->get('metadata'));
    }

    // Prevent caching if this is a mutation query.
    $request = $context->getRequest();
    if (!empty($request) && $request->hasMutations()) {
      $metadata->setCacheMaxAge(0);
    }

    return new QueryResult($processor->getResponseData(), $metadata);
  }

}