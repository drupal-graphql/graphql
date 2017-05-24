<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Component\Utility\NestedArray;
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
      $display = $view->getDisplay($displayId);

      if (!$type = $this->getEntityTypeByTable($view->get('base_table'))) {
        // Skip for now, switch to different response type later when
        // implementing fieldable views display support.
        continue;
      }

      $id = implode('-', [$viewId, $displayId, 'view']);

      $typeName = graphql_core_camelcase($type);
      $multi = TRUE;
      $paged = FALSE;
      $arguments = [];

      $filters = array_filter(NestedArray::getValue($display, ['display_options', 'filters']) ?: [], function ($sort) {
        return $sort['exposed'];
      });

      if ($filters) {
        $arguments['filter'] = [
          'type' => graphql_core_camelcase([
            $viewId, $displayId, 'view', 'filter', 'input',
          ]),
          'multi' => FALSE,
          'nullable' => TRUE,
        ];
      }


      $sorts = array_filter(NestedArray::getValue($display, ['display_options', 'sorts']) ?: [], function ($sort) {
        return $sort['exposed'];
      });

      if ($sorts) {
        $arguments += [
          'sortDirection' => [
            "type" => [
              "ASC" => "Ascending",
              "DESC" => "Descending",
            ],
            "default" => TRUE,
          ],
          'sortBy' => [
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
        $typeName = graphql_core_camelcase(implode('-', [
          $viewId, $displayId, 'result',
        ]));
        $multi = FALSE;
        $paged = TRUE;
        $arguments += [
          'page' => ['type' => 'Int', 'default' => $this->getPagerOffset($display)],
          'pageSize' => ['type' => 'Int', 'default' => $this->getPagerLimit($display)],
        ];
      }

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => graphql_core_propcase($id),
        'types' => ['Root'],
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
