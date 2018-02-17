<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use GraphQL\Server\Helper;
use GraphQL\Server\OperationParams;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;

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
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
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
   * @var \Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface
   */
  protected $queryProvider;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Processor constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface $queryProvider
   *   The query provider service.
   * @param array $parameters
   *   The graphql container parameters.
   */
  public function __construct(
    RendererInterface $renderer,
    AccountProxyInterface $currentUser,
    SchemaPluginManager $pluginManager,
    QueryProviderInterface $queryProvider,
    array $parameters
  ) {
    $this->renderer = $renderer;
    $this->currentUser = $currentUser;
    $this->pluginManager = $pluginManager;
    $this->queryProvider = $queryProvider;
    $this->parameters = $parameters;
    $this->helper = new Helper();
  }

  /**
   * Processes one or multiple graphql operations.
   *
   * @param string $schema
   *   The name of the schema to execute.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   * @param mixed $context
   *   The context for the query.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult
   *   The query result.
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

    return $this->executeQuery($server, $operations);
  }

  /**
   * Executes one or multiple graphql operations.
   *
   * @param \GraphQL\Server\ServerConfig $server
   *   The graphql server config.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult
   *   The result of executing the operations.
   */
  protected function executeQuery(ServerConfig $server, $operations) {
    // Evaluating the request might lead to rendering of markup which in turn
    // might "leak" cache metadata. Therefore, we execute the request within a
    // render context and collect the leaked metadata afterwards.
    $context = new RenderContext();
    /** @var \GraphQL\Executor\ExecutionResult|\GraphQL\Executor\ExecutionResult[] $result */
    $result = $this->renderer->executeInRenderContext($context, function() use ($server, $operations) {
      if (is_array($operations)) {
        return $this->helper->executeBatch($server, $operations);
      }

      return $this->helper->executeOperation($server, $operations);
    });

    $metadata = new CacheableMetadata();
    // Apply render context cache metadata to the response.
    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    return new QueryResult($result, $metadata);
  }

}
