<?php

namespace Drupal\graphql_content\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\qraphql_content\Traits\GraphQLEntityExposeTrait;

/**
 * Configuration form to define GraphQL schema content entity types and fields.
 */
class ContentEntitySchemaConfigForm extends ConfigFormBase {
  use GraphQLEntityExposeTrait;

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
    return 'content_entity_schema_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['graphql_content.schema'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Configure which content entity types, bundles and fields will be added to the GraphQL schema.'),
    ];

    $form['types'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Add interfaces and types'),
        $this->t('Attach fields from view mode'),
      ],
    ];

    /** @var EntityViewMode[] $modes */
    $modes = EntityViewMode::loadMultiple();

    foreach ($this->entityTypeManager->getDefinitions() as $type) {
      if ($type instanceof ContentEntityTypeInterface) {

        $config_name = 'graphql.exposed.' . $type->id();
        $config = \Drupal::configFactory()->getEditable($config_name);

        $form['types'][$type->id()]['exposed'] = [
          '#type' => 'checkbox',
          '#default_value' => $config->get('exposed'),
          '#title' => '<strong>' . $type->getLabel() . '</strong>',
          '#description' => $this->t('Add the <strong>%interface</strong> interface to the schema.', [
            '%interface' => StringHelper::camelCase($type->id()),
          ]),
          '#wrapper_attributes' => ['colspan' => 2, 'class' => ['highlight']],
        ];

        foreach ($this->bundleInfo->getBundleInfo($type->id()) as $bundle => $info) {
          $key = $type->id() . '__' . $bundle;

          $config_name = 'graphql.exposed.' . $type->id() . '.' . $bundle;
          $config = \Drupal::configFactory()->getEditable($config_name);

          $form['types'][$key]['exposed'] = [
            '#type' => 'checkbox',
            '#parents' => ['types', $type->id(), 'bundles', $bundle, 'exposed'],
            '#default_value' => $config->get('exposed'),
            '#states' => [
              'enabled' => [
                ':input[name="types[' . $type->id() . '][exposed]"]' => ['checked' => TRUE],
              ],
            ],
            '#title' => $info['label'],
            '#description' => $this->t('Add the <strong>%type</strong> type to the schema.', [
              '%type' => StringHelper::camelCase([$type->id(), $bundle]),
            ]),
          ];

          $options = [
            '__none__' => $this->t("Don't attach fields."),
            $type->id() . '.default' => $this->t('Default'),
          ];

          foreach ($modes as $mode) {
            /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
            if ($mode->getTargetType() == $type->id()) {
              $options[$mode->id()] = $mode->label();
            }
          }

          $form['types'][$key]['view_mode'] = [
            '#type' => 'select',
            '#parents' => [
              'types', $type->id(), 'bundles', $bundle, 'view_mode',
            ],
            '#default_value' => $config->get('view_mode'),
            '#options' => $options,
            '#attributes' => [
              'width' => '100%',
            ],
            '#states' => [
              'enabled' => [
                ':input[name="types[' . $type->id() . '][exposed]"]' => ['checked' => TRUE],
                ':input[name="types[' . $type->id() . '][bundles][' . $bundle . '][exposed]"]' => ['checked' => TRUE],
              ],
            ],
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

    // Sanitize boolean values.
    foreach (array_keys($types) as $entityType) {
      $exposed = (bool) $types[$entityType]['exposed'];
      $exposed ? $this->exposeEntity($entityType) : $this->unexposeEntity($entityType);

      if (!empty($types[$entityType]['bundles'])) {
        $bundles = array_keys($types[$entityType]['bundles']);
        foreach ($bundles as $bundle) {
          $bundle_config = $types[$entityType]['bundles'][$bundle];
          $exposed = (bool) $bundle_config['exposed'];
          $view_mode = $bundle_config['view_mode'];

          if ($exposed) {
            $this->exposeEntityBundle($entityType, $bundle, $view_mode);
          }
          else {
            $this->unexposeEntityBundle($entityType, $bundle);
          }
        }
      }
    }

    $this->invalidator->invalidateTags(['graphql_schema', 'graphql_request']);
    parent::submitForm($form, $form_state);
  }

}
