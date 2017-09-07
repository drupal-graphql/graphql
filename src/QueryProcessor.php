<?php

namespace Drupal\graphql;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Execution\Processor;
use Drupal\graphql\Reducers\ReducerManager;
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
   * The graphql schema.
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * The reducer manager service.
   *
   * @var \Drupal\graphql\Reducers\ReducerManager
   */
  protected $reducerManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The graphql container parameters.
   *
   * @var array
   */
  protected $parameters;

  public function __construct(
    ContainerInterface $container,
    AbstractSchema $schema,
    ReducerManager $reducerManager,
    AccountProxyInterface $currentUser,
    array $parameters
  ) {
    $this->container = $container;
    $this->schema = $schema;
    $this->currentUser = $currentUser;
    $this->parameters = $parameters;
    $this->reducerManager = $reducerManager;
  }

  /**
   * Execute a GraphQL query.
   *
   * @param string $query
   *   The GraphQL query.
   * @param array $variables
   *   The query variables.
   *
   * @return \Drupal\graphql\QueryResult
   *   The GraphQL query result.
   */
  public function processQuery($query, $variables = []) {
    $processor = new Processor($this->container, $this->schema, (
      $this->currentUser->hasPermission('bypass graphql field security')
      || $this->parameters['development']
      /*//
      TODO: Bypass security for persisted queries.
      //*/
    ));
    $processor->processPayload($query, $variables, $this->reducerManager->getAllServices());
    return new QueryResult($processor->getResponseData(), $processor);
  }

}