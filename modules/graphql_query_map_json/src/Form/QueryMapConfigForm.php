<?php

namespace Drupal\graphql_query_map_json\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure performance settings for this site.
 */
class QueryMapConfigForm extends ConfigFormBase {

  /**
   * The default cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Constructs a QueryMapConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The default cache backend.
   */
  public function __construct(ConfigFactoryInterface $configFactory, CacheBackendInterface $cacheBackend) {
    parent::__construct($configFactory);
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'graphql_query_map_json_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['graphql_query_map_json.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('graphql_query_map_json.config');

    $form['lookup_paths'] = [
      '#type' => 'textarea',
      '#title' => t('Lookup paths'),
      '#default_value' => implode($config->get('lookup_paths') ?: [], "\n"),
      '#description' => t('The path patterns to use for the query map lookup.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    $this->cacheBackend->delete('graphql_query_map_json_versions');

    $paths = array_map('trim', explode("\n", $formState->getValue('lookup_paths', '')));
    $this->config('graphql_query_map_json.config')
      ->set('lookup_paths', $paths)
      ->save();

    parent::submitForm($form, $formState);
  }

}
