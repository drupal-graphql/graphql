<?php

namespace Drupal\graphql\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Provides a style plugin for GraphQL views.
 *
 * @ViewsStyle(
 *   id = "graphql_fields",
 *   title = @Translation("GraphQL Fields"),
 *   help = @Translation("Returns a list of raw fields."),
 *   display_types = {"graphql"}
 * )
 */
class GraphQLFields extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesFields = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;

}
