<?php

/**
 * @file
 * Contains \Drupal\graphql\Plugin\views\display
 */

namespace Drupal\graphql_views\Plugin\views\display;

use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * Provides a display plugin for GraphQL views.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "graphql",
 *   title = @Translation("GraphQL"),
 *   help = @Translation("Creates a GraphQL entity list display."),
 *   admin = @Translation("GraphQL"),
 *   returns_response = TRUE
 * )
 */
class GraphQL extends DisplayPluginBase {
  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAJAX.
   */
  protected $usesAJAX = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesPager.
   */
  protected $usesPager = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesMore.
   */
  protected $usesMore = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAreas.
   */
  protected $usesAreas = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'graphql';
  }

  /**
   * {@inheritdoc}
   */
  public function usesFields() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function displaysExposed() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Set the default style plugin to 'graphql'.
    $options['style']['contains']['type']['default'] = 'graphql';
    $options['exposed_form']['contains']['type']['default'] = 'graphql';

    $options['defaults']['default']['style'] = FALSE;
    $options['defaults']['default']['exposed_form'] = FALSE;

    // Remove css/exposed form settings, as they are not used for the data display.
    unset($options['exposed_block']);
    unset($options['css_class']);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    unset($categories['format'], $categories['fields'], $categories['title']);
    unset($categories['pager'], $categories['exposed'], $categories['access']);

    unset($options['show_admin_links'], $options['analyze-theme'], $options['link_display']);
    unset($options['show_admin_links'], $options['analyze-theme'], $options['link_display']);

    unset($options['style'], $options['row'], $options['title'], $options['access']);
    unset($options['exposed_block'], $options['css_class']);
    unset($options['query'], $options['group_by']);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->view->execute();
  }
}
