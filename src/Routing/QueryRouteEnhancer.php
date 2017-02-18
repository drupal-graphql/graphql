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
    return $route->hasDefault('_graphql');
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    if (!empty($defaults['_controller'])) {
      return $defaults;
    }

    $values = $this->getValuesFromRequest($request);
    if ($enhanced = $this->enhanceSingle($defaults, $values)) {
      return $enhanced;
    }

    if ($enhanced = $this->enhanceBatch($defaults, $values)) {
      return $enhanced;
    }

    // By default we assume a 'single' request. This is going to fail in the
    // graphql processor due to a missing query string but at least provides
    // the right format for the client to act upon.ge
    return $defaults + [
      '_controller' => $defaults['_graphql']['single'],
    ];
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return array|mixed
   */
  protected function getValuesFromRequest(Request $request) {
    if ($content = $request->getContent()) {
      $values = json_decode($content, TRUE);
    } else {
      $values = $request->query->all();
    }

    // Only keep numerical keys or 'query', 'variables', 'id'.
    $values = array_filter($values, function ($value, $index) {
      return is_numeric($index) || in_array($index, ['query', 'variables', 'id']);
    }, ARRAY_FILTER_USE_BOTH);

    $values = array_map(function ($value) {
      if (is_string($value)) {
        return $value;
      }

      $decoded = json_decode($value, TRUE);
      return ($decoded != $value) && $decoded ? $decoded : $value;
    }, $values);

    return $values;
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
    $values = array_intersect_key($values + [
      'query' => '',
      'variables' => [],
      'id' => '',
    ], array_flip(['query', 'variables', 'id']));

    $query = $values['query'];
    $id = $values['id'];
    $variables = $values['variables'];
    if (empty($query) && empty($id)) {
      return FALSE;
    }

    // If a query id was provided, try loading a persisted query.
    if (empty($query) && !empty($id) && $json = file_get_contents(DRUPAL_ROOT . '/queries.json')) {
      $json = (array) json_decode($json);
      $query = array_search($id, $json) ?: NULL;
    }

    return $defaults + [
      'query' => is_string($query) ? $query : '',
      'variables' => is_array($variables) ? $variables : [],
      '_controller' => $defaults['_graphql']['single'],
    ];
  }
}