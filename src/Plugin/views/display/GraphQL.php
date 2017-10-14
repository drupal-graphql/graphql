<?php

/**
 * @file
 * Contains \Drupal\graphql\Plugin\views\display
 */

namespace Drupal\graphql\Plugin\views\display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\Utility\StringHelper;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a display plugin for GraphQL views.
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
    return TRUE;
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

    // Set the default plugins to 'graphql'.
    $options['style']['contains']['type']['default'] = 'graphql';
    $options['exposed_form']['contains']['type']['default'] = 'graphql';
    $options['row']['contains']['type']['default'] = 'graphql_entity';

    $options['defaults']['default']['style'] = FALSE;
    $options['defaults']['default']['exposed_form'] = FALSE;
    $options['defaults']['default']['row'] = FALSE;

    // Remove css/exposed form settings, as they are not used for the data display.
    unset($options['exposed_block']);
    unset($options['css_class']);

    $options['graphql_query_name'] = ['default' => ''];
    return $options;
  }

  /**
   * Get the user defined query name or the default one.
   *
   * @return string
   *   Query name.
   */
  public function getGraphQLQueryName() {
    return $this->getGraphQLName();
  }

  /**
   * Gets the result name based on user defined query name or the default one.
   *
   * @return string
   *   Result name.
   */
  public function getGraphQLResultName() {
    return $this->getGraphQLName('result', TRUE);
  }

  /**
   * Gets the row name based on user defined query name or the default one.
   *
   * @return string
   *   Row name.
   */
  public function getGraphQLRowName() {
    return $this->getGraphQLName('row', TRUE);
  }

  /**
   * Gets the filter input name based on user defined query name or the default one.
   *
   * @return string
   *   Result name.
   */
  public function getGraphQLFilterInputName() {
    return $this->getGraphQLName('filter_input', TRUE);
  }

  /**
   * Returns the id based on user-provided query name or the default one.
   *
   * @param string|null $suffix
   *   Id suffix, eg. row, result.
   * @param bool $type
   *   Whether to use camel- or snake case. Uses camel case if TRUE. Defaults to
   *   FALSE.
   *
   * @return string The id.
   *   The id.
   */
  public function getGraphQLName($suffix = NULL, $type = FALSE) {
    $queryName = strip_tags($this->getOption('graphql_query_name'));

    if (empty($queryName)) {
      $viewId = $this->view->id();
      $displayId = $this->display['id'];
      $parts = [$viewId, $displayId, 'view', $suffix];
      return $type ? StringHelper::camelCase($parts) : StringHelper::propCase($parts);
    }

    $parts = array_filter([$queryName, $suffix]);
    return $type ? StringHelper::camelCase($parts) : StringHelper::propCase($parts);
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    unset($categories['title']);
    unset($categories['pager'], $categories['exposed'], $categories['access']);

    unset($options['show_admin_links'], $options['analyze-theme'], $options['link_display']);
    unset($options['show_admin_links'], $options['analyze-theme'], $options['link_display']);

    unset($options['title'], $options['access']);
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

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = (!empty($this->view->result) || $this->view->style_plugin->evenEmpty()) ? $this->view->style_plugin->render($this->view->result) : [];

    // Apply the cache metadata from the display plugin. This comes back as a
    // cache render array so we have to transform it back afterwards.
    $this->applyDisplayCachablityMetadata($this->view->element);

    return [
      'view' => $this->view,
      'rows' => $rows,
      'cache' => CacheableMetadata::createFromRenderArray($this->view->element),
    ];
  }
}
