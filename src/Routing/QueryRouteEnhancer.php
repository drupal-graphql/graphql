<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class QueryRouteEnhancer implements RouteEnhancerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return $route->getPath() === '/graphql';
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    $body = (array) json_decode($request->getContent()) + [
      'query' => NULL,
      'variables' => NULL,
      'id' => NULL,
    ];

    $query = $request->query->has('query') ? $request->query->get('query') : $body['query'];
    $variables = $request->query->has('variables') ? $request->query->get('variables') : $body['variables'];
    $id = $request->query->has('id') ? $request->query->get('id') : $body['id'];
    $variables = ($variables && is_string($variables) ? json_decode($variables) : $variables);
    $variables = (array) ($variables ?: []);

    // If a query id was provided, try loading a persisted query.
    // @todo Make the queries.json input configurable.
    if (empty($query) && !empty($id) && $json = file_get_contents(DRUPAL_ROOT . '/queries.json')) {
      $json = (array) json_decode($json);
      $query = array_search($id, $json);
    }

    return [
      'query' => $query,
      'variables' => $variables,
    ] + $defaults;
  }
}