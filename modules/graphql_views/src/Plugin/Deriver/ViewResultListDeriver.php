<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\views\Views;

/**
 * Derive fields from configured views.
 */
class ViewResultListDeriver extends ViewDeriverBase {

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

      $id = implode('-', [$viewId, $displayId, 'result', 'list']);

      $typeName = graphql_core_camelcase($type);

      if (!$this->interfaceExists($typeName)) {
        $typeName = 'Entity';
      }

      $this->derivatives[$id] = [
        'id' => $id,
        'name' => 'results',
        'type' => $typeName,
        'types' => [
          graphql_core_camelcase(implode('_', [$viewId, $displayId, 'result'])),
        ],
        'multi' => TRUE,
        'view' => $viewId,
        'display' => $displayId,
        'cache_tags' => $view->getCacheTags(),
        'cache_contexts' => $view->getCacheContexts(),
        'cache_max_age' => $view->getCacheMaxAge(),
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
