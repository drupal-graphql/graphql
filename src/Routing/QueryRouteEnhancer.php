<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Route;

class QueryRouteEnhancer implements RouteEnhancerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return $route->hasDefault('_graphql');
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    if (!empty($defaults['_controller'])) {
      return $defaults;
    }

    $method = $request->getMethod();

    if ($method === 'POST') {
      $values = (array) json_decode($request->getContent());
      return $this->doEnhance($defaults, $values);
    }

    if ($method === 'GET') {
      $values = $request->query->all();
      return $this->doEnhance($defaults, $values);
    }

    return $defaults;
  }

  /**
   * @param $defaults
   * @param array $values
   * @return mixed
   */
  protected function doEnhance($defaults, array $values) {
    if ($enhanced = $this->enhanceSingle($defaults, $values)) {
      return $enhanced;
    }

    if ($enhanced = $this->enhanceBatch($defaults, $values)) {
      return $enhanced;
    }

    // By default we assume a 'single' request. This is going to fail in the
    // graphql processor due to a missing query string but at least provides
    // the right format for the client to act upon.
    return $defaults + [
      '_controller' => $defaults['_graphql']['single'],
    ];
  }

  /**
   * @param $defaults
   * @param $values
   * @return bool
   */
  protected function enhanceBatch($defaults, $values) {
    if (!isset($values[0])) {
      return FALSE;
    }

    $queries = array_filter($values, function ($value, $index) {
      return is_numeric($index);
    }, ARRAY_FILTER_USE_BOTH);

    if (array_keys($queries) !== range(0, count($queries) - 1)) {
      // If this is not a continuously numeric array, don't do anything.
      return FALSE;
    }

    $queries = array_map(function ($query) {
      return  (array) json_decode($query);
    }, $queries);

    return $defaults + [
      '_controller' => $defaults['_graphql']['multiple'],
      'queries' => $queries,
    ];
  }

  /**
   * @param $defaults
   * @param $values
   * @return bool
   */
  protected function enhanceSingle($defaults, $values) {
    $values = $values + [
      'query' => NULL,
      'variables' => NULL,
      'id' => NULL,
    ];

    if (empty($values['query']) && empty($values['id'])) {
      return FALSE;
    }

    if (!$query = $this->getQuery($values['query'], $values['id'])) {
      return FALSE;
    }

    $variables = $this->getVariables($values['variables']);

    return $defaults + [
      'query' => $query,
      'variables' => $variables,
      '_controller' => $defaults['_graphql']['single'],
    ];
  }

  /**
   * @param $query
   * @param $id
   * @return null
   */
  protected function getQuery($query, $id) {
    // If a query id was provided, try loading a persisted query.
    // @todo Make the queries.json input configurable.
    if (empty($query) && !empty($id) && $json = file_get_contents(DRUPAL_ROOT . '/queries.json')) {
      $json = (array) json_decode($json);
      $query = array_search($id, $json) ?: NULL;
    }

    return $query;
  }

  /**
   * @param $variables
   * @return array|mixed
   */
  protected function getVariables($variables) {
    $variables = ($variables && is_string($variables) ? json_decode($variables) : $variables);
    $variables = (array) ($variables ?: []);

    return $variables;
  }
}