<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;

class QueryProcessor {

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
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\graphql\GraphQL\Schema\SchemaLoader $schemaLoader
   *   The schema loader service.
   * @param array $parameters
   *   The graphql container parameters.
   */
  public function __construct(
    SchemaLoader $schemaLoader,
    AccountProxyInterface $currentUser,
    array $parameters
  ) {
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

    // Set up the processor with parameters to be used in the resolvers.
    $processor = new Processor($schema);
    $context = $processor->getExecutionContext();
    $container = $context->getContainer();
    $secure = !!($bypassSecurity || $this->currentUser->hasPermission('bypass graphql field security') || $this->parameters['development']);
    $container->set('secure', $secure);
    $container->set('metadata', new CacheableMetadata());

    // Run the query against the parser.
    $result = $processor->processPayload($query, $variables)->getResponseData();

    // Add collected cache metadata from the query processor.
    $responseCacheMetadata = new CacheableMetadata();
    if ($container->has('metadata') && ($metadata = $container->get('metadata'))) {
      if ($metadata instanceof CacheableDependencyInterface) {
        $responseCacheMetadata->addCacheableDependency($metadata);
      }
    }

    // Prevent caching if this is a mutation query or an error occurred.
    $request = $context->getRequest();
    if ((!empty($request) && $request->hasMutations()) || $context->hasErrors()) {
      $responseCacheMetadata->setCacheMaxAge(0);
    }

    // Load the schema's cache metadata.
    $schemaCacheMetadata = $this->schemaLoader->getResponseCacheMetadata($schemaId);
    return new QueryResult($result, $responseCacheMetadata, $schemaCacheMetadata);
  }

}
