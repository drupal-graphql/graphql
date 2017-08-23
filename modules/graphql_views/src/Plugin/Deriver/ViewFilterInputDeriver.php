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

      //Re-key $filters by filter_identifier
      $newFilters = [];
      foreach ($filters as $key => $value) {
        $newFilters[$value['expose']['identifier']] = $value;
      }
      $filters = $newFilters;

      $fields = array_map(function ($filter) use ($basePluginDefinition) {
        if ($this->isGenericInputFilter($filter)) {
          return $this->createGenericInputFilterDefinition($filter, $basePluginDefinition);
        }

        return [
          'type' => 'String',
          'nullable' => TRUE,
          'multi' => $filter['expose']['multiple'],
        ];
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

  /**
   * Checks if a filter definition is a generic input filter.
   *
   * @param mixed $filter
   *   $filter['value'] = [];
   *   $filter['value'] = [
   *     "text",
   *     "test"
   *   ];
   *   $filter['value'] = [
   *     'distance' => 10,
   *     'distance2' => 30,
   *     ...
   *   ];
   * @return bool
   */
   public function isGenericInputFilter($filter) {
     if (!is_array($filter['value']) || count($filter['value']) == 0) {
       return false;
     }

     $firstKey = array_keys($filter['value'])[0];
     return is_string($firstKey);
   }

  /**
   * Creates a definition for a generic input filter.
   *
   * @param mixed $filter
   *   $filter['value'] = [];
   *   $filter['value'] = [
   *     "text",
   *     "test"
   *   ];
   *   $filter['value'] = [
   *     'distance' => 10,
   *     'distance2' => 30,
   *     ...
   *   ];
   * @param mixed $basePluginDefinition
   * @return array
   */
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
      'name' => graphql_camelcase($id),
      'fields' => $fields,
    ] + $basePluginDefinition;

    $this->derivatives[$id] = $genericInputFilter;

    return [
      'type' => $genericInputFilter['name'],
      'nullable' => TRUE,
      'multi' => $filter['expose']['multiple'],
    ];
  }
}
