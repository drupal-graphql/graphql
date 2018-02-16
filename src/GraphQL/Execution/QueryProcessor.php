<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * QueryProcessor constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $pluginManager
   *   The schema plugin manager.
   * @param \Drupal\graphql\QueryProvider\QueryProviderInterface $queryProvider
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
   * Execute a GraphQL query.
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

    // Evaluating the request can potentially invoke rendering. We allow those
    // to "leak" and collect them here in a render context.
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
