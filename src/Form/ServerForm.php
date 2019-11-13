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
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxSchemaConfigurationForm(array $form) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#edit-schema-configuration-plugin-wrapper', $form['schema_configuration']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState) {
    $form = parent::form($form, $formState);
    /** @var \Drupal\graphql\Entity\ServerInterface $server */
    $server = $this->entity;
    $schemas = array_map(function ($definition) {
      return $definition['name'] ?? $definition['id'];
    }, $this->schemaManager->getDefinitions());
    $schema = ($formState->getUserInput()['schema'] ?? $server->get('schema')) ?: reset(array_keys($schemas));

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
      '#options' => $schemas,
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
      '#type' => 'container',
      '#prefix' => '<div id="edit-schema-configuration-plugin-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
    $instance = $schema ? $this->schemaManager->createInstance($schema) : NULL;
    if ($instance instanceof PluginFormInterface && $instance instanceof ConfigurableInterface) {
      $instance->setConfiguration($server->get('schema_configuration')[$schema] ?? []);

      $form['schema_configuration'][$schema] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Schema configuration'),
        '#tree' => TRUE,
      ];

      $form['schema_configuration'][$schema] += $instance->buildConfigurationForm([], $formState);
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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function validateForm(array &$form, FormStateInterface $formState) {
    $endpoint = &$formState->getValue('endpoint');

    // Trim the submitted value of whitespace and slashes. Ensure to not trim
    // the slash on the left side.
    $endpoint = rtrim(trim(trim($endpoint), ''), "\\/");
    if ($endpoint[0] !== '/') {
      $formState->setErrorByName('endpoint', 'The endpoint path has to start with a forward slash.');
    }
    elseif (!UrlHelper::isValid($endpoint)) {
      $formState->setErrorByName('endpoint', 'The endpoint path contains invalid characters.');
    }

    /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
    $schema = $formState->getValue('schema');
    $instance = $this->schemaManager->createInstance($schema);
    if (!empty($form['schema_configuration'][$schema]) && $instance instanceof PluginFormInterface && $instance instanceof ConfigurableInterface) {
      $state = SubformState::createForSubform($form['schema_configuration'][$schema], $form, $formState);
      $instance->validateConfigurationForm($form['schema_configuration'][$schema], $state);
    }
  }

  public function submitForm(array &$form, FormStateInterface $formState) {
    parent::submitForm($form, $formState);

    /* @var \Drupal\graphql\Plugin\SchemaPluginInterface $instance */
    $schema = $formState->getValue('schema');
    $instance = $this->schemaManager->createInstance($schema);
    if ($instance instanceof PluginFormInterface && $instance instanceof  ConfigurableInterface) {
      $state = SubformState::createForSubform($form['schema_configuration'][$schema], $form, $formState);
      $instance->submitConfigurationForm($form['schema_configuration'][$schema], $state);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function save(array $form, FormStateInterface $formState) {
    parent::save($form, $formState);

    $this->messenger()->addMessage($this->t('Saved the %label server.', [
      '%label' => $this->entity->label(),
    ]));

    $formState->setRedirect('entity.graphql_server.collection');
  }

}
