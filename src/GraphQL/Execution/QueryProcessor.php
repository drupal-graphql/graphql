<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ResolveInfo;

class QueryProcessor {

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

  /**
   * The schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager
   */
  protected $pluginManager;

  /**
   * QueryProcessor constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param array $parameters
   *   The graphql container parameters.
   */
  public function __construct(
    SchemaPluginManager $pluginManager,
    AccountProxyInterface $currentUser,
    array $parameters
  ) {
    $this->pluginManager = $pluginManager;
    $this->currentUser = $currentUser;
    $this->parameters = $parameters;
  }

  /**
   * Execute a GraphQL query.
   *
   * @param string $id
   *   The name of the schema to process the query against.
   * @param string $query
   *   The GraphQL query.
   * @param array $variables
   *   The query variables.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult.
   *   The GraphQL query result.
   */
  public function processQuery($id, $query, array $variables = []) {
    $debug = !empty($this->parameters['development']);

    try {
      $schema = $this->pluginManager->createInstance($id)->getSchema();
      $result = GraphQL::executeQuery($schema, $query, NULL, NULL, $variables, NULL);
      $output = $result->toArray($debug);
    }
    catch (\Exception $error) {
      $output = ['errors' => FormattedError::createFromException($error, $debug)];
    }

    return new QueryResult($output, new CacheableMetadata());
  }

}
