<?php

namespace Drupal\graphql\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\graphql\Plugin\PersistedQueryPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin form to set up persisted GraphQL queries.
 */
class PersistedQueriesForm extends EntityForm {

  /**
   * Plugin manager for persisted query plugins.
   *
   * @var \Drupal\graphql\Plugin\PersistedQueryPluginManager
   */
  protected $persistedQueryPluginManager;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\graphql\Entity\Server
   */
  protected $entity;

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
  public static function create(ContainerInterface $container): self {
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
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\graphql\Plugin\PersistedQueryPluginInterface[] $plugins */
    $plugins = $this->entity->getPersistedQueryInstances();
    $all_plugins = $this->getAllPersistedQueryPlugins();
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'graphql/persisted_queries';
    $form['enabled'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enabled persisted query plugins'),
      '#attributes' => [
        'class' => [
          'persisted-queries-enabled-wrapper',
        ],
      ],
    ];
    foreach ($all_plugins as $id => $plugin) {
      $clean_css_id = Html::cleanCssIdentifier($plugin->getPluginId());
      $form['enabled'][$plugin->getPluginId()] = [
        '#type' => 'checkbox',
        '#title' => $plugin->label(),
        '#description' => $plugin->getDescription(),
        '#default_value' => !empty($plugins[$id]),
        '#attributes' => [
          'class' => [
            'persisted-queries-enabled-' . $clean_css_id,
          ],
          'data-id' => $clean_css_id,
        ],
      ];
    }

    // Set the weights of the persisted query plugins.
    $form['weights'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Order'),
    ];
    $form['weights']['order'] = [
      '#type' => 'table',
    ];
    $form['weights']['order']['#tabledrag'][] = [
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'persisted-query-plugin-weight',
    ];
    $plugins_weight = [];
    foreach ($all_plugins as $plugin_id => $plugin) {
      $plugins_weight[$plugin_id] = !empty($plugins[$plugin_id]) ? $plugins[$plugin_id]->getWeight() : $plugin->getWeight();
    }
    asort($plugins_weight);
    foreach ($plugins_weight as $id => $weight) {
      $plugin = $all_plugins[$id];
      $form['weights']['order'][$id]['#attributes']['class'][] = 'draggable';
      $form['weights']['order'][$id]['#attributes']['class'][] = 'persisted-queries-weight--' . Html::cleanCssIdentifier($id);
      $form['weights']['order'][$id]['#weight'] = $weight;
      $form['weights']['order'][$id]['label']['#plain_text'] = $plugin->label();
      $form['weights']['order'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for persisted query plugin %title', ['%title' => $plugin->label()]),
        '#title_display' => 'invisible',
        '#delta' => 50,
        '#default_value' => $weight,
        '#attributes' => [
          'class' => [
            'persisted-query-plugin-weight',
          ],
        ],
      ];
    }

    // Add vertical tabs containing the settings for the plugins.
    $form['plugin_settings'] = [
      '#title' => $this->t('Persisted queries settings'),
      '#type' => 'vertical_tabs',
    ];

    foreach ($plugins_weight as $plugin_id => $weight) {
      $plugin = !empty($plugins[$plugin_id]) ? $plugins[$plugin_id] : $all_plugins[$plugin_id];
      if ($plugin instanceof PluginFormInterface) {
        $form['settings'][$plugin_id] = [
          '#type' => 'details',
          '#title' => $plugin->label(),
          '#group' => 'plugin_settings',
          '#attributes' => [
            'class' => [
              'persisted-queries-settings--' . Html::cleanCssIdentifier($plugin_id),
            ],
          ],
        ];
        $plugin_form_state = SubformState::createForSubform($form['settings'][$plugin_id], $form, $form_state);
        $form['settings'][$plugin_id] += $plugin->buildConfigurationForm($form['settings'][$plugin_id], $plugin_form_state);
      }
      else {
        unset($form['settings'][$plugin_id]);
      }
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

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();

    $plugins = $this->getAllPersistedQueryPlugins();
    foreach ($plugins as $id => $plugin) {
      if (empty($values['enabled'][$id])) {
        $this->entity->removePersistedQueryInstance($id);
        continue;
      }
      if ($plugin instanceof PluginFormInterface) {
        $plugin_form_state = SubformState::createForSubform($form['settings'][$id], $form, $form_state);
        $plugin->submitConfigurationForm($form['settings'][$id], $plugin_form_state);
      }
      // Use isset instead of empty to cover weight with zero index.
      if (isset($values['weights']['order'][$id]['weight'])) {
        $plugin->setWeight((int) $values['weights']['order'][$id]['weight']);
      }
      $this->entity->addPersistedQueryInstance($plugin);
    }
  }

  /**
   * Returns an array with all the available persisted query plugins.
   *
   * @return \Drupal\graphql\Plugin\PersistedQueryPluginInterface[]
   */
  protected function getAllPersistedQueryPlugins() {
    $plugins = [];
    foreach ($this->persistedQueryPluginManager->getDefinitions() as $id => $definition) {
      $plugins[$id] = $this->persistedQueryPluginManager->createInstance($id);
    }
    return $plugins;
  }

}
