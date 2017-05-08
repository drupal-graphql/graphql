<?php

namespace Drupal\graphql_views\Plugin\views\exposed_form;

use Drupal\views\Plugin\views\exposed_form\ExposedFormPluginBase;

/**
 * Exposed form plugin that prevents any rendering.
 *
 * @ingroup views_exposed_form_plugins
 *
 * @ViewsExposedForm(
 *   id = "graphql",
 *   title = @Translation("GraphQL"),
 *   help = @Translation("Prevents rendering of exposed forms")
 * )
 */
class GraphQL extends ExposedFormPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderExposedForm($block = FALSE) {
    // We don't render a form. Due to this, we won't have a form state which is
    // otherwise required by views to read the exposed form values from. Hence,
    // we need to manually write these values.
    $this->view->exposed_data = $this->view->getExposedInput();

    return NULL;
  }
}
