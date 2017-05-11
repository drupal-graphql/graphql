<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class QueryRouteEnhancer implements RouteEnhancerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new QueryRouteEnhancer instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

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

    if ($enhanced = $this->enhanceSingle($defaults, $request)) {
      return $enhanced;
    }

    if ($enhanced = $this->enhanceBatch($defaults, $request)) {
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
   * Attempts to enhance the request as a batch query.
   *
   * @param array $defaults
   *   The controller defaults.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array|boolean
   *   The enhanced controller defaults.
   */
  protected function enhanceBatch(array $defaults, Request $request) {
    $queries = $this->filterRequestValues($request, function ($index) {
      return is_numeric($index);
    });

    if (!isset($queries[0])) {
      return FALSE;
    }

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
   * Attempts to enhance the request as a single query.
   *
   * @param array $defaults
   *   The controller defaults.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array|boolean
   *   The enhanced controller defaults.
   */
  protected function enhanceSingle(array $defaults, Request $request) {
    $values = $this->filterRequestValues($request, function ($index) {
       return in_array($index, ['query', 'variables', 'id', 'version']);
    }) + [
      'query' => '',
      'variables' => [],
      'id' => NULL,
      'version' => NULL,
    ];

    if (empty($values['query']) && (empty($values['id']) || empty($values['version']))) {
      return FALSE;
    }

    if (!$query = $this->getQuery($values['query'], $values['id'], $values['version'])) {
      return FALSE;
    }

    return $defaults + [
      'query' => is_string($query) ? $query : '',
      'variables' => is_array($values['variables']) ? $values['variables'] : [],
      '_controller' => $defaults['_graphql']['single'],
    ];
  }

  /**
   * Filters the request body or query parameters using a filter callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param callable $filter
   *   The filter callback.
   *
   * @return array
   *   The filtered request parameters.
   */
  protected function filterRequestValues(Request $request, callable $filter) {
    $content = $request->getContent();

    $values = !empty($content) ? json_decode($content, TRUE) : $request->query->all();
    $values = array_filter($values, $filter, ARRAY_FILTER_USE_KEY);
    $values = array_map(function ($value) {
      if (!is_string($value)) {
        return $value;
      }

      $decoded = json_decode($value, TRUE);
      return ($decoded != $value) && $decoded ? $decoded : $value;
    }, $values);

    return $values;
  }

  /**
   * Resolves a query string.
   *
   * @param $query
   *   The query string. If this is set, it will be returned immediately.
   * @param $id
   *   The id of a query from the query map.
   * @param $version
   *   The version of the query map to load the query from.
   *
   * @return string|null
   *   The resolved query string.
   */
  protected function getQuery($query, $id, $version) {
    if (!empty($query)) {
      return $query;
    }

    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('graphql_query_map');
    /** @var \Drupal\graphql\Entity\GraphQLQueryMap $queryMap */
    if ($queryMap = $storage->load($version)) {
      return $queryMap->getQuery($id);
    }

    return NULL;
  }

}
