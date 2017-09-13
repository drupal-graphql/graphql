<?php

namespace Drupal\graphql\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Provides a style plugin for GraphQL views.
 *
 * @ViewsStyle(
 *   id = "graphql",
 *   title = @Translation("GraphQL"),
 *   help = @Translation("Provides entity or field rows to GraphQL."),
 *   display_types = {"graphql"}
 * )
 */
class GraphQL extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesFields = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }
    unset($this->view->row_index);

    return $rows;
  }
}
