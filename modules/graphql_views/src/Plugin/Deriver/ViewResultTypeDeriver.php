<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\views\Views;

/**
 * Derive fields from configured views.
 */
class ViewResultTypeDeriver extends ViewDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $viewStorage = $this->entityTypeManager->getStorage('view');

    foreach (Views::getApplicableViews('graphql_display') as list($viewId, $displayId)) {
      /** @var \Drupal\views\ViewEntityInterface $view */
      $view = $viewStorage->load($viewId);
      $display = $this->getViewDisplay($view, $displayId);

      if (!$this->isPaged($display)) {
        // Skip if the display doesn't expose a pager.
        continue;
      }

      if (!$type = $this->getEntityTypeByTable($view->get('base_table'))) {
        // Skip for now, switch to different response type later when
        // implementing fieldable views display support.
        continue;
      }

      $id = implode('-', [$viewId, $displayId, 'result']);

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => graphql_camelcase($id),
        'view' => $viewId,
        'display' => $displayId,
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
