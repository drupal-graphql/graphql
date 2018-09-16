<?php

namespace Drupal\graphql\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   */
  public function __construct(SchemaPluginManager $schemaManager, RequestContext $requestContext) {
    $this->requestContext = $requestContext;
    $this->schemaManager = $schemaManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.graphql.schema'),
      $container->get('router.request_context')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formStaet) {
    $form = parent::form($form, $formStaet);

    /** @var \Drupal\graphql\Entity\ServerInterface $server */
    $server = $this->entity;
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
      '#default_value' => $server->get('schema'),
      '#description' => t('The schema to use with this server.'),
    ];

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
    else if (!UrlHelper::isValid($endpoint)) {
      $formState->setErrorByName('endpoint', 'The endpoint path contains invalid characters.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $formState) {
    parent::save($form, $formState);

    drupal_set_message($this->t('Saved the %label server.', [
      '%label' => $this->entity->label(),
    ]));

    $formState->setRedirect('entity.graphql_server.collection');
  }

}
