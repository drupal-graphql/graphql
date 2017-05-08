<?php

namespace Drupal\graphql_views\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "graphql",
 *   title = @Translation("GraphQL"),
 *   help = @Translation("Returns a list of raw entity ids ."),
 *   display_types = {"graphql"}
 * )
 */
class GraphQL extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;

  /**
   * {@inheritdoc}
   */
  public function render() {
    return '';
  }
}
