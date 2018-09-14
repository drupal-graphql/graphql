<?php

namespace Drupal\graphql\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

class ServerForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formStaet) {
    $form = parent::form($form, $formStaet);

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

    $form['endpoint'] = [
      '#title' => t('Endpoint'),
      '#type' => 'textfield',
      '#default_value' => $server->endpoint,
      '#description' => t('The endpoint for http queries.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['batching'] = [
      '#title' => t('Allow query batching'),
      '#type' => 'checkbox',
      '#default_value' => $server->batching,
      '#description' => t('Whether batched queries are allowed.'),
    ];

    $form['debug'] = [
      '#title' => t('Enable debugging'),
      '#type' => 'checkbox',
      '#default_value' => $server->debug,
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
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $formState) {
    /** @var \Drupal\graphql\Entity\ServerInterface $entity */
    $entity = $this->entity;
    $entity->set('name', $formState->getValue('name'));
    $entity->set('label', $formState->getValue('label'));
    $entity->set('endpoint', $formState->getValue('endpoint'));
    $entity->set('debug', $formState->getValue('debug'));
    $entity->set('batching', $formState->getValue('batching'));
    $entity->save();

    drupal_set_message($this->t('Saved the %label server.', [
      '%label' => $entity->label(),
    ]));

    $formState->setRedirect('entity.graphql_server.collection');
  }

}
