<?php

namespace Drupal\graphql_query_map_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for GraphQL query map forms.
 */
class GraphQLQueryMapForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $formState = NULL) {
    $form = parent::buildForm($form, $formState);
    $form['#title'] = $this->t('Query map version %version', ['%version' => $this->entity->id()]);

    foreach ($this->entity->queryMap as $i => $query) {
      $form['queryMap'][$i] = [
        '#title' => $this->t('ID %id', ['%id' => $i]),
        '#type' => 'textarea',
        '#default_value' => $query,
        '#disabled' => TRUE,
        '#rows' => 15,
      ];
    }

    $actions['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Back'),
      '#url' => $this->entity->toUrl('collection'),
    ];

    $form['actions'] = $actions;

    return $form;
  }

}
