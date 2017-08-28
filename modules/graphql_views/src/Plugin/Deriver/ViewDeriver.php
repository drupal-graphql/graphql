<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
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

      $typeName = StringHelper::camelCase($type);
      $multi = TRUE;
      $paged = FALSE;
      $arguments = [];
      $types = ['Root'];

      $filters = array_filter($display->getOption('filters') ?: [], function($filter) {
        return array_key_exists('exposed', $filter) && $filter['exposed'];
      });

      if (!empty($filters)) {
        $arguments['filter'] = [
          'type' => StringHelper::camelCase([
            $viewId, $displayId, 'view', 'filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];
      }

      $argumentsInfo = $this->getArgumentsInfo($display->getOption('arguments') ?: []);
      if (!empty($argumentsInfo)) {
        $arguments['contextualFilter'] = [
          'type' => StringHelper::camelCase([
            $viewId, $displayId, 'view', 'contextual', 'filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];

        foreach ($argumentsInfo as $argumentInfo) {
          // Depending on whether bundles are known, we expose the view field
          // either on the interface (e.g. Node) or on the type (e.g. NodePage)
          // level. Here we specify types managed by other graphql_* modules,
          // yet we don't define these modules as dependencies. If types are not
          // in the schema, the resulting GraphQL field will be attached to
          // nowhere, so it won't go into the schema.
          if (empty($argumentInfo['bundles']) && empty($argumentInfo['entity_type'])) {
            continue;
          }

          if (empty($argumentInfo['bundles'])) {
            $types = array_merge($types, [StringHelper::camelCase($argumentInfo['entity_type'])]);
          }
          else {
            $types = array_merge($types, array_map(function($bundle) use ($argumentInfo) {
              return StringHelper::camelCase([$argumentInfo['entity_type'], $bundle]);
            }, $argumentInfo['bundles']));
          }
        }
      }

      $sorts = array_filter($display->getOption('sorts') ?: [], function($sort) {
        return $sort['exposed'];
      });

      if (!empty($sorts)) {
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
            "enum_type_name" => StringHelper::camelCase(['sort', 'by', $id, 'enum']),
            "type" => array_map(function($sort) {
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
        $typeName = StringHelper::camelCase([$viewId, $displayId, 'result']);
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
