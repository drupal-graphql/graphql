<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\views\Views;

/**
 * Derive fields from configured views.
 */
class ViewFilterInputDeriver extends ViewDeriverBase implements ContainerDeriverInterface {

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

      $id = implode('_', [$viewId, $displayId, 'view', 'filter', 'input']);

      $filters = array_filter($display->getOption('filters') ?: [], function ($filter) {
        return array_key_exists('exposed', $filter) && $filter['exposed'];
      });

      // If there are no exposed filters, don't create the derivative.
      if (!$filters) {
        continue;
      }

      $fields = array_map(function ($filter) use ($basePluginDefinition) {
        if ( $this->isGenericInputFilter($filter) ) {
          return $this->createGenericInputFilterDefinition($filter, $basePluginDefinition);
        } else {
          return [
            'type' => 'String',
            'nullable' => TRUE,
            'multi' => $filter['expose']['multiple'],
          ];
        }
      }, $filters);

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => graphql_camelcase($id),
        'fields' => $fields,
        'view' => $viewId,
        'display' => $displayId,
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }


  public function isGenericInputFilter($filter) {
    if (!is_array($filter['value'])) {
      return false;
    }

    if (count($filter['value']) == 0) {
      return false;
    }

    return is_string( array_keys($filter['value'] )[0] );
  }


  public function createGenericInputFilterDefinition($filter, $basePluginDefinition) {

    $filterId = $filter['expose']['identifier'];

    $id = implode('_', [
      $filter['expose']['multiple'] ? $filterId : $filterId . '_multi',
      'view',
      'filter',
      'input'
    ]);

    $fields = [];
    foreach ($filter['value'] as $fieldKey => $fieldDefaultValue) {
      $fields[ $fieldKey ] = [
        'type' => 'String',
        'nullable' => TRUE,
        'multi' => FALSE,
      ];
    }


    $genericInputFilter = [
      'id' => $id,
      'name' => graphql_core_camelcase($id),
      'fields' => $fields,
      'view' => $viewId,
      'display' => $displayId,
    ] + $basePluginDefinition;

    $this->derivatives[$id] = $genericInputFilter;

    return [
      'type' => $genericInputFilter['name'],
      'nullable' => TRUE,
      'multi' => $filter['expose']['multiple'],
    ];
  }

}
