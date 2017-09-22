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
use Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig;


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
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo,
    CacheTagsInvalidatorInterface $invalidator,
    ContentEntityMutationSchemaConfig $schemaConfig
  ) {
    parent::__construct($configFactory);
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
    $this->invalidator = $invalidator;
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('cache_tags.invalidator'),
      $container->get('graphql_content_mutation.schema_config')
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
    $form = parent::buildForm($form, $form_state);

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

    // UI config to enable/disable mutation checkboxes on unexposed items.
    $restrictMutations = empty(\Drupal::config('graphql_content_mutation.settings')->get('allow_all_mutations'));

    foreach ($this->entityTypeManager->getDefinitions() as $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $entityType = $type->id();
        $form['types'][$entityType]['label'] = [
          '#type' => 'html_tag',
          '#tag' => 'strong',
          '#value' => $type->getLabel(),
          '#wrapper_attributes' => ['class' => ['highlight']],
        ];

        $restrictEntityMutations = $restrictMutations && !$this->schemaConfig->isEntityTypeExposed($entityType);
        $form['types'][$entityType]['delete'] = [
          '#type' => 'checkbox',
          '#parents' => ['types', $entityType, 'delete'],
          '#default_value' => $this->schemaConfig->isDeleteExposed($entityType),
          '#title' => $this->t('Delete'),
          '#disabled' => $restrictEntityMutations,
        ];

        foreach ($this->bundleInfo->getBundleInfo($entityType) as $bundle => $info) {
          $key = $entityType . '__' . $bundle;

          $form['types'][$key]['exposed'] = [
            '#markup' => $info['label'],
          ];

          $form['types'][$key]['operations'] = [
            '#type' => 'container',
            '#wrapper_attributes' => ['class' => ['form--inline']],
          ];

          $restrictEntityBundleMutations = $restrictMutations && !$this->schemaConfig->isEntityBundleExposed($entityType, $bundle);
          $form['types'][$key]['operations']['create'] = [
            '#type' => 'checkbox',
            '#parents' => ['types', $entityType, 'bundles', $bundle, 'create'],
            '#default_value' => $this->schemaConfig->isCreateExposed($entityType, $bundle),
            '#title' => $this->t('Create'),
            '#disabled' => $restrictEntityBundleMutations,
          ];

          $form['types'][$key]['operations']['update'] = [
            '#type' => 'checkbox',
            '#parents' => ['types', $entityType, 'bundles', $bundle, 'update'],
            '#default_value' => $this->schemaConfig->isUpdateExposed($entityType, $bundle),
            '#title' => $this->t('Update'),
            '#disabled' => $restrictEntityBundleMutations,
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
    $types = $form_state->getValue('types');

    foreach (array_keys($types) as $entityType) {
      $mutations = [];
      if ($types[$entityType]['delete']) {
        $mutations[] = 'delete';
      }
      $this->schemaConfig->exposeEntityMutations($entityType, $mutations);
      if (!empty($types[$entityType]['bundles'])) {
        $bundles = array_keys($types[$entityType]['bundles']);
        foreach ($bundles as $bundle) {
          $bundle_config = $types[$entityType]['bundles'][$bundle];
          $mutations = [];
          if ($bundle_config['create']) {
            $mutations[] = 'create';
          }
          if ($bundle_config['update']) {
            $mutations[] = 'update';
          }

          $this->schemaConfig->exposeEntityBundleMutations($entityType, $bundle, $mutations);
        }
      }
    }

    $this->invalidator->invalidateTags(['graphql_schema', 'graphql_request']);
    parent::submitForm($form, $form_state);
  }

}
