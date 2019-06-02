<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Registers graphql query routes for all schemas.
 */
class QueryRoutes extends RouteSubscriberBase {

  /**
   * The graphql schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * The authentication collector.
   *
   * @var \Drupal\Core\Authentication\AuthenticationCollectorInterface
   */
  protected $authenticationCollector;

  /**
   * QueryRoutes constructor.
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $schemaManager
   *   The graphql schema plugin manager.
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $authenticationCollector
   *   The authentication collector.
   */
  public function __construct(SchemaPluginManager $schemaManager, AuthenticationCollectorInterface $authenticationCollector) {
    $this->schemaManager = $schemaManager;
    $this->authenticationCollector = $authenticationCollector;
  }

  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();
    $providers = $this->authenticationCollector->getSortedProviders();
    $providerIds = array_keys($providers);

    foreach ($this->schemaManager->getDefinitions() as $key => $definition) {
      $routes->add("graphql.query.$key", new Route($definition['path'], [
        'schema' => $key,
        '_graphql' => TRUE,
        '_controller' => '\Drupal\graphql\Controller\RequestController::handleRequest',
        '_disable_route_normalizer' => 'TRUE',
      ], [
        '_graphql_query_access' => 'TRUE',
      ], [
        '_auth' => $providerIds,
      ]));
    }

    $collection->addCollection($routes);
  }

}
