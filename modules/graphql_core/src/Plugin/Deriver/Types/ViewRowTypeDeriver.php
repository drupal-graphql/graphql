<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\graphql_core\Plugin\Deriver\ViewDeriverBase;
use Drupal\views\Views;

/**
 * Derive row types from configured fieldable views.
 */
class ViewRowTypeDeriver extends ViewDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    if ($this->entityTypeManager->hasDefinition('view')) {
      $viewStorage = $this->entityTypeManager->getStorage('view');

      foreach (Views::getApplicableViews('graphql_display') as list($viewId, $displayId)) {
        /** @var \Drupal\views\ViewEntityInterface $view */
        $view = $viewStorage->load($viewId);
        if (!$this->getRowResolveType($view, $displayId)) {
          continue;
        }

        $style = $this->getViewStyle($view, $displayId);
        // This deriver only supports style plugins that use fields.
        if (!$style->usesFields()) {
          continue;
        }

        /** @var \Drupal\graphql\Plugin\views\display\GraphQL $display */
        $display = $this->getViewDisplay($view, $displayId);

        $id = implode('-', [$viewId, $displayId, 'row']);
        $this->derivatives[$id] = [
            'id' => $id,
            'name' => $display->getGraphQLRowName(),
            'view' => $viewId,
            'display' => $displayId,
          ] + $this->getCacheMetadataDefinition($view) + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
