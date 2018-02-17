<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Registers routes for all configurable schemas.
 */
class ConfigurationRoutes extends RouteSubscriberBase {

  /**
   * The graphql schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * ConfigurationRoutes constructor.
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
      if (empty($definition['uses_plugins'])) {
        continue;
      }

      $routes->add("graphql.configuration.$key", new Route("{$definition['path']}/configure", [
        'schema' => $key,
        '_controller' => '\Drupal\graphql\Controller\ConfigurationController::configurationOverview',
        '_title' => 'Configuration',
      ], [
        '_admin_route' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
