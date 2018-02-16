<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Drupal\graphql\QueryProvider\QueryProviderInterface;
use GraphQL\Error\FormattedError;
use GraphQL\Error\UserError;
use GraphQL\GraphQL;
use GraphQL\Server\Helper;
use GraphQL\Server\OperationParams;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
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
   * The server helper.
   *
   * @var \GraphQL\Server\Helper
   */
  protected $helper;

  /**
   * The query provider service.
   *
   * @var \Drupal\graphql\QueryProvider\QueryProviderInterface
   */
  protected $queryProvider;

  /**
   * QueryProcessor constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\QueryProvider\QueryProviderInterface $queryProvider
   *   The query provider service.
   * @param array $parameters
   *   The graphql container parameters.
   */
  public function __construct(
    SchemaPluginManager $pluginManager,
    AccountProxyInterface $currentUser,
    QueryProviderInterface $queryProvider,
    array $parameters
  ) {
    $this->pluginManager = $pluginManager;
    $this->currentUser = $currentUser;
    $this->queryProvider = $queryProvider;
    $this->parameters = $parameters;
    $this->helper = new Helper();
  }

  /**
   * Execute a GraphQL query.
   *
   * @param string $schema
   *   The name of the schema to execute.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   * @param mixed $context
   *   The context for the query.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult .
   *   The GraphQL query result.
   */
  public function processQuery($schema, $operations, $context = NULL) {
    $debug = !empty($this->parameters['development']);

    // Load the plugin from the schema manager.
    $plugin = $this->pluginManager->createInstance($schema);
    $schema = $plugin->getSchema();

    // Create the server config.
    $server = ServerConfig::create();
    $server->setDebug($debug);
    $server->setSchema($schema);
    $server->setContext($context);
    $server->setQueryBatching(TRUE);
    $server->setPersistentQueryLoader(function ($id, OperationParams $operation) {
      if ($query = $this->queryProvider->getQuery($id, $operation)) {
        return $query;
      }

      throw new RequestError(sprintf("Failed to load query map for id '%s'.", $id));
    });

    if (is_array($operations)) {
      $output = $this->helper->executeBatch($server, $operations);
    }
    else {
      $output = $this->helper->executeOperation($server, $operations);
    }

    return new QueryResult($output->toArray($debug), new CacheableMetadata());
  }

}
