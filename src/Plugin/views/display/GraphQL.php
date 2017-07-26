<?php

/**
 * @file
 * Contains \Drupal\graphql\Plugin\views\display
 */

namespace Drupal\graphql\Plugin\views\display;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\FormStateInterface;

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
 *   graphql_display = TRUE,
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

    $options['graphql_query_name'] = ['default' => ''];
    return $options;
  }

  public function getGraphQLQueryName() {
    $graphql_query_name = strip_tags($this->getOption('graphql_query_name'));
    if (empty($graphql_query_name)) {
      $viewId = $this->view->id();
      $displayId = $this->display['id'];
      // @todo: fix PHP fatal when graphql_core is not installed.
      // fix: move this GraphQL display plugin under graphql_views module.
      $graphql_query_name = graphql_core_camelcase([$viewId, $displayId, 'view']);
    }
    return lcfirst($graphql_query_name);
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

    $categories['graphql'] = [
      'title' => $this->t('GraphQL'),
      'column' => 'second',
      'build' => [
        '#weight' => -10,
      ],
    ];

    $options['graphql_query_name'] = [
      'category' => 'graphql',
      'title' => $this->t('Query name'),
      'value' => views_ui_truncate($this->getGraphQLQueryName(), 24),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    switch ($form_state->get('section')) {
      case 'graphql_query_name':
        $form['#title'] .= $this->t('Query name');
        $form['graphql_query_name'] = [
          '#type' => 'textfield',
          '#description' => $this->t('This will be the graphQL query name.'),
          '#default_value' => $this->getGraphQLQueryName(),
        ];
        break;
     }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);
    $section = $form_state->get('section');
    switch ($section) {
      case 'graphql_query_name':
        $this->setOption($section, $form_state->getValue($section));
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->view->execute();
  }
}
