<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\views\Plugin\views\display\DisplayPluginInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\views\Views;

/**
 * Derive fields from configured views.
 */
class ViewDeriver extends ViewDeriverBase implements ContainerDeriverInterface {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $viewStorage = $this->entityTypeManager->getStorage('view');

    foreach (Views::getApplicableViews('graphql_display') as list($viewId, $displayId)) {
      /** @var \Drupal\views\ViewEntityInterface $view */
      $view = $viewStorage->load($viewId);
      if (!$this->getRowResolveType($view, $displayId)) {
        continue;
      }

      /** @var \Drupal\graphql\Plugin\views\display\GraphQL $display */
      $display = $this->getViewDisplay($view, $displayId);

      $id = implode('-', [$viewId, $displayId, 'view']);
      $info = $this->getArgumentsInfo($display->getOption('arguments') ?: []);
      $arguments = [];
      $arguments += $this->getContextualArguments($info, $id);
      $arguments += $this->getPagerArguments($display);
      $arguments += $this->getSortArguments($display, $id);
      $arguments += $this->getFilterArguments($display, $id);
      $types = $this->getTypes($info);

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => $display->getGraphQLQueryName(),
        'type' => $display->getGraphQLResultName(),
        'parents' => $types,
        'multi' => FALSE,
        'arguments' => $arguments,
        'view' => $viewId,
        'display' => $displayId,
        'paged' => $this->isPaged($display),
        'arguments_info' => $info,
      ] + $this->getCacheMetadataDefinition($view) + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Helper function to return the contextual filter argument if any exist.
   *
   * @param array $arguments
   *   The array of available arguments.
   * @param $id
   *   The plugin derivative id.
   *
   * @return array
   *   The contextual filter argument if applicable.
   */
  protected function getContextualArguments(array $arguments, $id) {
    if (!empty($arguments)) {
      return [
        'contextualFilter' => [
          'type' => StringHelper::camelCase([$id, 'contextual', 'filter', 'input']),
          'multi' => FALSE,
          'nullable' => TRUE,
        ],
      ];
    }

    return [];
  }

  /**
   * Helper function to retrieve the sort arguments if any are exposed.
   *
   * @param \Drupal\views\Plugin\views\display\DisplayPluginInterface $display
   *   The display plugin.
   * @param $id
   *   The plugin derivative id.
   *
   * @return array
   *   The sort arguments if any exposed sorts are available.
   */
  protected function getSortArguments(DisplayPluginInterface $display, $id) {
    $sorts = array_filter($display->getOption('sorts') ?: [], function($sort) {
      return $sort['exposed'];
    });

    return !empty($sorts) ? [
      'sortDirection' => [
        'enum_type_name' => 'ViewsSortDirectionEnum',
        'type' => [
          'ASC' => 'Ascending',
          'DESC' => 'Descending',
        ],
        'default' => TRUE,
      ],
      'sortBy' => [
        'enum_type_name' => StringHelper::camelCase(['sort', 'by', $id, 'enum']),
        'type' => array_map(function($sort) {
          return $sort['expose']['label'];
        }, $sorts),
        'nullable' => TRUE,
      ],
    ] : [];
  }

  /**
   * Helper function to return the filter argument if applicable.
   *
   * @param \Drupal\views\Plugin\views\display\DisplayPluginInterface $display
   *   The display plugin.
   * @param $id
   *   The plugin derivative id.
   *
   * @return array
   *   The filter argument if any exposed filters are available.
   */
  protected function getFilterArguments(DisplayPluginInterface $display, $id) {
    $filters = array_filter($display->getOption('filters') ?: [], function($filter) {
      return array_key_exists('exposed', $filter) && $filter['exposed'];
    });

    return !empty($filters) ? [
      'filter' => [
        'type' => StringHelper::camelCase([$id, 'filter', 'input']),
        'multi' => FALSE,
        'nullable' => TRUE,
      ],
    ] : [];
  }

  /**
   * Helper function to retrieve the pager arguments if the display is paged.
   *
   * @param \Drupal\views\Plugin\views\display\DisplayPluginInterface $display
   *   The display plugin.
   *
   * @return array
   *   An array of pager arguments if the view display is paged.
   */
  protected function getPagerArguments(DisplayPluginInterface $display) {
    return $this->isPaged($display) ? [
      'page' => ['type' => 'Int', 'default' => $this->getPagerOffset($display)],
      'pageSize' => ['type' => 'Int', 'default' => $this->getPagerLimit($display)],
    ] : [];
  }

  /**
   * Helper function to retrieve the types that the view can be attached to.
   *
   * @param array $arguments
   *   An array containing information about the available arguments.
   * @return array
   *   An array of additional types the view can be embedded in.
   */
  protected function getTypes(array $arguments) {
    $types = ['Root'];

    if (empty($arguments)) {
      return $types;
    }

    foreach ($arguments as $argument) {
      // Depending on whether bundles are known, we expose the view field
      // either on the interface (e.g. Node) or on the type (e.g. NodePage)
      // level. Here we specify types managed by other graphql_* modules,
      // yet we don't define these modules as dependencies. If types are not
      // in the schema, the resulting GraphQL field will be attached to
      // nowhere, so it won't go into the schema.
      if (empty($argument['bundles']) && empty($argument['entity_type'])) {
        continue;
      }

      if (empty($argument['bundles'])) {
        $types = array_merge($types, [StringHelper::camelCase($argument['entity_type'])]);
      }
      else {
        $types = array_merge($types, array_map(function($bundle) use ($argument) {
          return StringHelper::camelCase([$argument['entity_type'], $bundle]);
        }, array_keys($argument['bundles'])));
      }
    }

    return $types;
  }

}
