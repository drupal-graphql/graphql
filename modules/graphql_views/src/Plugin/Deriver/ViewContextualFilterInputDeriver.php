<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\views\Views;

/**
 * Derive input types for view contextual filters.
 */
class ViewContextualFilterInputDeriver extends ViewDeriverBase implements ContainerDeriverInterface {

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

      $argumentsInfo = $this->getArgumentsInfo($display->getOption('arguments') ?: []);
      if ($argumentsInfo) {
        $id = implode('_', [
          $viewId, $displayId, 'view', 'contextual_filter', 'input'
        ]);
        $this->derivatives[$id] = [
          'id' => $id,
          'name' => graphql_camelcase($id),
          'fields' => array_fill_keys(array_keys($argumentsInfo), [
            'type' => 'String',
            // Always expose contextual filters as nullable. Let views module
            // decide what to do if value is missing.
            'nullable' => TRUE,
            'multi' => FALSE,
          ]),
          'view' => $viewId,
          'display' => $displayId,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
