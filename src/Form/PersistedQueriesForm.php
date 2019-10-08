<?php

namespace Drupal\graphql\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\graphql\Plugin\PersistedQueryPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PersistedQueriesForm extends EntityForm {

  protected $persistedQueryPluginManager;

  /**
   * PersistedQueriesForm constructor.
   *
   * @param \Drupal\graphql\Plugin\PersistedQueryPluginManager $persistedQueryPluginManager
   */
  public function __construct(PersistedQueryPluginManager $persistedQueryPluginManager) {
    $this->persistedQueryPluginManager = $persistedQueryPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.graphql.persisted_query')
    );
  }


  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'graphql_persisted_queries_form';
  }

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $plugins = $this->persistedQueryPluginManager->getDefinitions();
    $form['#tree'] = TRUE;
    foreach ($plugins as $id => $plugin) {
      $form['persisted_query_plugins'][$id] = [
        '#type' => 'checkbox',
        '#title' => $plugin['name'],
        '#description' => $plugin['description'],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

}
