<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Registers graphql explorer routes for all schemas.
 */
class ExplorerRoutes extends RouteSubscriberBase {

  /**
   * The graphql schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * ExplorerRoutes constructor.
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
      $routes->add("graphql.explorer.$key", new Route("{$definition['path']}/explorer", [
        'schema' => $key,
        '_controller' => '\Drupal\graphql\Controller\ExplorerController::viewExplorer',
        '_title' => 'GraphiQL',
      ], [
        '_permission' => 'use graphql explorer',
      ], [
        '_admin_route' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
