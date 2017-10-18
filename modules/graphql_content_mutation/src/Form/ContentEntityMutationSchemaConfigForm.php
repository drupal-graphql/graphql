<?php

namespace Drupal\graphql_content_mutation\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form to define GraphQL schema content mutation.
 */
class ContentEntityMutationSchemaConfigForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $invalidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo,
    CacheTagsInvalidatorInterface $invalidator
  ) {
    parent::__construct($configFactory);
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
    $this->invalidator = $invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_entity_mutation_schema_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['graphql_content_mutation.schema'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('This form is deprecated and configuration. Settings will not be respected by the schema any more.', 'error'));
    $form = parent::buildForm($form, $form_state);
    $defaults = [];
    $config = $this->config('graphql_content_mutation.schema');
    if ($config) {
      $defaults = $config->get('types');
    }

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Configure which entity mutations will be added to the GraphQL schema.'),
    ];

    $form['types'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Entity bundles'),
        $this->t('Exposed operations'),
      ],
    ];

    foreach ($this->entityTypeManager->getDefinitions() as $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $form['types'][$type->id()]['label'] = [
          '#type' => 'html_tag',
          '#tag' => 'strong',
          '#value' => $type->getLabel(),
          '#wrapper_attributes' => ['class' => ['highlight']],
        ];

        $form['types'][$type->id()]['delete'] = [
          '#type' => 'checkbox',
          '#parents' => ['types', $type->id(), 'delete'],
          '#default_value' => isset($defaults[$type->id()]['delete']) ? $defaults[$type->id()]['delete'] : [],
          '#title' => $this->t('Delete'),
        ];

        foreach ($this->bundleInfo->getBundleInfo($type->id()) as $bundle => $info) {
          $key = $type->id() . '__' . $bundle;

          $form['types'][$key]['exposed'] = [
            '#markup' => $info['label'],
          ];

          $form['types'][$key]['operations'] = [
            '#type' => 'container',
            '#wrapper_attributes' => ['class' => ['form--inline']],
          ];

          $form['types'][$key]['operations']['create'] = [
            '#type' => 'checkbox',
            '#parents' => ['types', $type->id(), 'bundles', $bundle, 'create'],
            '#default_value' => isset($defaults[$type->id()]['bundles'][$bundle]['create']) ? $defaults[$type->id()]['bundles'][$bundle]['create'] : [],
            '#title' => $this->t('Create'),
          ];

          $form['types'][$key]['operations']['update'] = [
            '#type' => 'checkbox',
            '#parents' => ['types', $type->id(), 'bundles', $bundle, 'update'],
            '#default_value' => isset($defaults[$type->id()]['bundles'][$bundle]['update']) ? $defaults[$type->id()]['bundles'][$bundle]['update'] : [],
            '#title' => $this->t('Update'),
          ];
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('graphql_content_mutation.schema')
      ->set('types', $form_state->getValue('types'))
      ->save();

    $this->invalidator->invalidateTags(['graphql_schema', 'graphql_request']);
    parent::submitForm($form, $form_state);
  }

}
