<?php

namespace Drupal\graphql\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for GraphQL query map forms.
 */
class EntityQueryMapForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $formState = NULL) {
    $form = parent::buildForm($form, $formState);
    $form['#title'] = $this->t('Query map version %version', ['%version' => $this->entity->id()]);

    /** @var \Drupal\graphql\Entity\QueryMapInterface $entity */
    $entity = $this->entity;

    foreach ($entity->get('map') as $id => $query) {
      $form['map'][$id] = [
        '#title' => $this->t('ID %id', ['%id' => $id]),
        '#type' => 'textarea',
        '#default_value' => $query,
        '#disabled' => TRUE,
        '#rows' => 15,
      ];
    }

    $form['actions'] = [
      'delete' => [
        '#type' => 'link',
        '#title' => $this->t('Back'),
        '#url' => $entity->toUrl('collection'),
      ],
    ];

    return $form;
  }

}
