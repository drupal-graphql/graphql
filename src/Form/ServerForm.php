<?php

namespace Drupal\graphql\Form;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @package Drupal\graphql\Form
 *
 * @codeCoverageIgnore
 */
class ServerForm extends EntityForm {

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * The schema plugin manager.
   *
   * @var \Drupal\graphql\Plugin\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * ServerForm constructor.
   *
   * @param \Drupal\graphql\Plugin\SchemaPluginManager $schemaManager
   *   The schema plugin manager.
   * @param \Drupal\Core\Routing\RequestContext $requestContext
   *   The request context.
   *
   * @codeCoverageIgnore
   */
  public function __construct(SchemaPluginManager $schemaManager, RequestContext $requestContext) {
    $this->requestContext = $requestContext;
    $this->schemaManager = $schemaManager;
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.graphql.schema'),
      $container->get('router.request_context')
    );
  }

  /**
   * Ajax callback triggered by the type schema select element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxSchemaConfigurationForm(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#edit-schema-configuration-plugin-wrapper', $form['schema_configuration'][$form_state->getValue('schema')]));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState) {
    $form = parent::form($form, $formState);
    /** @var \Drupal\graphql\Entity\ServerInterface $server */
    $server = $this->entity;
    $userInput = $formState->getUserInput();
    $schema = (!empty($userInput['schema'])) ? $userInput['schema'] : $server->get('schema');

    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add server');
    }
    else {
      $form['#title'] = $this->t('Edit %label server', ['%label' => $server->label()]);
    }

    $form['label'] = [
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $server->label(),
      '#description' => t('The human-readable name of this server.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['name'] = [
      '#type' => 'machine_name',
      '#default_value' => $server->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => ['Drupal\graphql\Entity\Server', 'load'],
        'source' => ['label'],
      ],
      '#description' => t('A unique machine-readable name for this server. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['schema'] = [
      '#title' => t('Schema'),
      '#type' => 'select',
      '#options' => array_map(function ($definition) {
        return $definition['name'] ?? $definition['id'];
      }, $this->schemaManager->getDefinitions()),
      '#default_value' => $schema,
      '#description' => t('The schema to use with this server.'),
      '#ajax' => [
        'callback' => '::ajaxSchemaConfigurationForm',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Updating schema configuration form.'),
        ],
      ],
    ];

    $form['schema_configuration'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Schema configuration'),
      '#tree' => TRUE,
    ];

    if (!empty($schema)) {
      /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
      $instance = $this->schemaManager->createInstance($schema);

      $form['schema_configuration'][$instance->getPluginId()] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'edit-schema-configuration-plugin-wrapper',
        ],
      ];

      if ($instance instanceof PluginFormInterface && $instance instanceof ConfigurableInterface) {
        $schemaConfiguration = $server->get('schema_configuration') ?? [];
        $pluginConfiguration = (!empty($schemaConfiguration) && !empty($schemaConfiguration[$schema])) ? $schemaConfiguration[$schema] : [];
        $instance->setConfiguration($pluginConfiguration);

        $form['schema_configuration'][$instance->getPluginId()] += $instance->buildConfigurationForm([], $formState);
      }
      else {
        $form['schema_configuration'][$instance->getPluginId()] += ['#markup' => $this->t("This schema doesn't have a configuration form.")];
      }
    }

    $form['endpoint'] = [
      '#title' => t('Endpoint'),
      '#type' => 'textfield',
      '#default_value' => $server->get('endpoint'),
      '#description' => t('The endpoint for http queries. Has to start with a forward slash. For example "/graphql".'),
      '#required' => TRUE,
      '#size' => 30,
      '#field_prefix' => $this->requestContext->getCompleteBaseUrl(),
    ];

    $form['batching'] = [
      '#title' => t('Allow query batching'),
      '#type' => 'checkbox',
      '#default_value' => !!$server->get('batching'),
      '#description' => t('Whether batched queries are allowed.'),
    ];

    $form['caching'] = [
      '#title' => t('Enable caching'),
      '#type' => 'checkbox',
      '#default_value' => !!$server->get('caching'),
      '#description' => t('Whether caching of queries and partial results is enabled.'),
    ];

    $form['debug'] = [
      '#title' => t('Enable debugging'),
      '#type' => 'checkbox',
      '#default_value' => !!$server->get('debug'),
      '#description' => t('In debugging mode, error messages contain more verbose information in the query response.'),
    ];

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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState) {
    $endpoint = &$formState->getValue('endpoint');

    // Trim the submitted value of whitespace and slashes. Ensure to not trim
    // the slash on the left side.
    $endpoint = rtrim(trim(trim($endpoint), ''), "\\/");
    if ($endpoint[0] !== '/') {
      $formState->setErrorByName('endpoint', 'The endpoint path has to start with a forward slash.');
    }
    else {
      if (!UrlHelper::isValid($endpoint)) {
        $formState->setErrorByName('endpoint', 'The endpoint path contains invalid characters.');
      }
    }

    /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
    $instance = $this->schemaManager->createInstance($formState->getValue('schema'));
    if (!empty($form['schema_configuration'][$instance->getPluginId()]) && $instance instanceof PluginFormInterface && $instance instanceof ConfigurableInterface) {
      $schema_form_state = SubformState::createForSubform($form['schema_configuration'][$instance->getPluginId()], $form, $formState);
      $instance->validateConfigurationForm($form['schema_configuration'][$instance->getPluginId()], $schema_form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $formState) {
    parent::save($form, $formState);

    $this->messenger()->addMessage($this->t('Saved the %label server.', [
      '%label' => $this->entity->label(),
    ]));

    $formState->setRedirect('entity.graphql_server.collection');

    /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
    $instance = $this->schemaManager->createInstance($formState->getValue('schema'));
    if ($instance instanceof PluginFormInterface && $instance instanceof  ConfigurableInterface) {
      $schema_form_state = SubformState::createForSubform($form['schema_configuration'][$instance->getPluginId()], $form, $formState);
      $instance->submitConfigurationForm($form['schema_configuration'][$instance->getPluginId()], $schema_form_state);
    }
  }

}
