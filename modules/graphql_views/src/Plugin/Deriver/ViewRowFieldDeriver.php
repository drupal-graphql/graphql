<?php

namespace Drupal\graphql_views\Plugin\Deriver;

use Drupal\graphql\Plugin\views\row\GraphQLFieldRow;
use Drupal\graphql\Utility\StringHelper;
use Drupal\views\Views;

/**
 * Derive row fields from configured fieldable views.
 */
class ViewRowFieldDeriver extends ViewDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $viewStorage = $this->entityTypeManager->getStorage('view');

    foreach (Views::getApplicableViews('graphql_display') as list($viewId, $displayId)) {
      /** @var \Drupal\views\ViewEntityInterface $view */
      $view = $viewStorage->load($viewId);
      /** @var \Drupal\graphql\Plugin\views\display\GraphQL $display */
      $display = $this->getViewDisplay($view, $displayId);
      $rowPlugin = $display->getPlugin('row');

      // This deriver only supports our custom field row plugin.
      if (!$rowPlugin instanceof GraphQLFieldRow) {
        continue;
      }

      foreach ($display->getHandlers('field') as $name => $field) {
        $id = implode('-', [$viewId, $displayId, 'field', $name]);
        $alias = $rowPlugin->getFieldKeyAlias($name);

        $this->derivatives[$id] = [
          'id' => $id,
          'name' => $alias,
          'type' => 'String',
          'parents' => [$display->getGraphQLRowName()],
          'view' => $viewId,
          'display' => $displayId,
          'field' => $alias,
        ] + $this->getCacheMetadataDefinition($view) + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
