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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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

    $values = $this->getValuesFromRequest($request);
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
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return array|mixed
   */
  protected function getValuesFromRequest(Request $request) {
    if ($content = $request->getContent()) {
      $values = json_decode($content, TRUE);
    } else {
      $values = $request->query->all();
    }

    // Only keep numerical keys or 'query', 'variables', 'id', 'version'.
    $values = array_filter($values, function ($value, $index) {
      return is_numeric($index) || in_array($index, ['query', 'variables', 'id', 'version']);
    }, ARRAY_FILTER_USE_BOTH);

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
      'id' => NULL,
      'version' => NULL,
    ], array_flip(['query', 'variables', 'id', 'version']));

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
   * @param $query
   * @param $id
   * @param $version
   * @return null
   */
  protected function getQuery($query, $id, $version) {
    if (!empty($query)) {
      return $query;
    }

    $queryMap = $this->entityTypeManager->getStorage('graphql_query_map')->load($version);
    if ($queryMap) {
      return $queryMap->getQuery($id);
    }

    return NULL;
  }

}
