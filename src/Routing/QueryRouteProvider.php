<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\graphql\Entity\Server;
use Symfony\Component\Routing\Route;

class QueryRouteProvider {

  /**
   * The authentication collector service.
   *
   * @var \Drupal\Core\Authentication\AuthenticationCollectorInterface
   */
  protected $authenticationCollector;

  /**
   * QueryRouteProvider constructor.
   *
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $authenticationCollector
   *   The authentication collector service.
   */
  public function __construct(AuthenticationCollectorInterface $authenticationCollector) {
    $this->authenticationCollector = $authenticationCollector;
  }

  /**
   * Collects routes for the server endpoints.
   */
  public function routes() {
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = Server::loadMultiple();
    $routes = [];

    // Allow all authentication providers by default.
    $auth = array_keys($this->authenticationCollector->getSortedProviders());

    foreach ($servers as $id => $server) {
      $path = $server->get('endpoint');

      $routes["graphql.query.$id"] = (new Route($path))
        ->addDefaults([
          'server' => $id,
          '_graphql' => TRUE,
          '_controller' => '\Drupal\graphql\Controller\RequestController::handleRequest',
          '_disable_route_normalizer' => TRUE,
        ])
        ->addRequirements([
          '_graphql_query_access' => 'TRUE',
          '_format' => 'json',
        ])
        ->addOptions([
          '_auth' => $auth,
          'no_cache' => TRUE,
          'default_url_options' => ['path_processing' => FALSE],
          'parameters' => ['server' => ['type' => 'entity:graphql_server']]
        ]);
    }

    return $routes;
  }

}
