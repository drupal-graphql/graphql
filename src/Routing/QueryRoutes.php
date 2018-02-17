<?php

namespace Drupal\graphql\Routing;

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
   * QueryRoutes constructor.
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $schemaManager
   *   The graphql schema plugin manager.
   */
  public function __construct(SchemaPluginManager $schemaManager) {
    $this->schemaManager = $schemaManager;
  }

  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();

    foreach ($this->schemaManager->getDefinitions() as $key => $definition) {
      $routes->add("graphql.query.$key", new Route($definition['path'], [
        'schema' => $key,
        '_graphql' => TRUE,
        '_controller' => '\Drupal\graphql\Controller\RequestController::handleRequest',
      ], [
        '_graphql_query_access' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
