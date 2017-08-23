<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
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
      $display = $this->getViewDisplay($view, $displayId);

      if (!$type = $this->getEntityTypeByTable($view->get('base_table'))) {
        // Skip for now, switch to different response type later when
        // implementing fieldable views display support.
        continue;
      }

      $id = implode('-', [$viewId, $displayId, 'view']);

      $typeName = graphql_camelcase($type);
      $multi = TRUE;
      $paged = FALSE;
      $arguments = [];
      $types = ['Root'];

      $filters = array_filter($display->getOption('filters') ?: [], function ($filter) {
        return array_key_exists('exposed', $filter) && $filter['exposed'];
      });

      if ($filters) {
        $arguments['filter'] = [
          'type' => graphql_camelcase([
            $viewId, $displayId, 'view', 'filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];
      }

      $argumentsInfo = $this->getArgumentsInfo($display->getOption('arguments') ?: []);
      if ($argumentsInfo) {
        $arguments['contextual_filter'] = [
          'type' => graphql_camelcase([
            $viewId, $displayId, 'view', 'contextual_filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];
        foreach ($argumentsInfo as $argumentInfo) {
          // 1) Depending on whether bundles are known, we expose the view field
          // either on the interface (e.g. Node) or on the type (e.g. NodePage)
          // level.
          // 2) Here we specify types managed by other graphql_* modules, yet we
          // don't define these modules as dependencies. If types are not in the
          // schema, the resulting GraphQL field will be attached to nowhere, so
          // it won't go into the schema.
          $argumentTypes = empty($argumentInfo['bundles'])
            ? [graphql_camelcase($argumentInfo['entity_type'])]
            : array_map(function ($bundle) use ($argumentInfo) {
              return graphql_camelcase([$argumentInfo['entity_type'], $bundle]);
            }, $argumentInfo['bundles']);
          $types = array_merge($types, $argumentTypes);
        }
      }

      $sorts = array_filter($display->getOption('sorts') ?: [], function ($sort) {
        return $sort['exposed'];
      });

      if ($sorts) {
        $arguments += [
          'sortDirection' => [
            "enum_type_name" => "ViewsSortDirectionEnum",
            "type" => [
              "ASC" => "Ascending",
              "DESC" => "Descending",
            ],
            "default" => TRUE,
          ],
          'sortBy' => [
            "enum_type_name" => graphql_camelcase(['SortBy', $id, 'Enum']),
            "type" => array_map(function ($sort) {
              return $sort['expose']['label'];
            }, $sorts),
            "nullable" => TRUE,
          ],
        ];
      }

      if (!$this->interfaceExists($typeName)) {
        $typeName = 'Entity';
      }

      // If a pager is configured we apply the matching ViewResult derivative
      // instead of the entity list.
      if ($this->isPaged($display)) {
        $typeName = graphql_camelcase(implode('-', [
          $viewId, $displayId, 'result',
        ]));
        $multi = FALSE;
        $paged = TRUE;
        $arguments += [
          'page' => ['type' => 'Int', 'default' => $this->getPagerOffset($display)],
          'pageSize' => ['type' => 'Int', 'default' => $this->getPagerLimit($display)],
        ];
      }

      $name = $display->getGraphQLQueryName();
      $this->derivatives[$id] = [
        'id' => $id,
        'name' => $name,
        'types' => $types,
        'arguments_info' => $argumentsInfo,
        'type' => $typeName,
        'multi' => $multi,
        'arguments' => $arguments,
        'view' => $viewId,
        'display' => $displayId,
        'paged' => $paged,
        'cache_tags' => $view->getCacheTags(),
        'cache_contexts' => $view->getCacheContexts(),
        'cache_max_age' => $view->getCacheMaxAge(),
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
