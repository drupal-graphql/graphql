<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\graphql\Utility\StringHelper;
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
      if (!$type = $this->getRowResolveType($view, $displayId)) {
        continue;
      }

      /** @var \Drupal\graphql\Plugin\views\display\GraphQL $display */
      $display = $this->getViewDisplay($view, $displayId);

      $id = implode('-', [$viewId, $displayId, 'result', 'list']);
      $style = $this->getViewStyle($view, $displayId);
      $this->derivatives[$id] = [
        'id' => $id,
        'type' => $type,
        'parents' => [$display->getGraphQLResultName()],
        'multi' => TRUE,
        'view' => $viewId,
        'display' => $displayId,
        'uses_fields' => $style->usesFields(),
      ] + $this->getCacheMetadataDefinition($view) + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
