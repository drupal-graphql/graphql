<?php

namespace Drupal\graphql_query_map_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\graphql_query_map_entity\Entity\GraphQLQueryMap;

/**
 * Form controller for GraphQL query map forms.
 */
class GraphQLQueryMapImportForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState) {
    $form = parent::form($form, $formState);

    $form['#title'] = $this->t('Import query map');

    $form['query_map_json'] = [
      '#type' => 'file',
      '#title' => $this->t('Query map'),
      '#description' => $this->t('Upload a query map .json file.'),
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
    $files = file_save_upload('query_map_json', [
      'file_validate_extensions' => ['json'], // Validate extensions.
    ]);

    if (!isset($files[0])) {
      $formState->setError($form['query_map_json'], $this->t('No file was uploaded.'));
    }

    /** @var \Drupal\file\Entity\File $file */
    $file = $files[0];
    // Save the file for use in the submit handler.
    $formState->set('file', $file);

    $queryMapJson = file_get_contents($file->getFileUri());
    $version = sha1($queryMapJson);
    if (GraphQLQueryMap::exists($version)) {
      $formState->setError($form['query_map_json'], $this->t('A query map with the same queries already exists.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $formState) {
    $file = $formState->get('file');
    $queryMapJson = file_get_contents($file->getFileUri());

    $graphqlQueryMap = $this->entity;
    $graphqlQueryMap->version = sha1($queryMapJson);
    $graphqlQueryMap->queryMap = array_flip((array) json_decode($queryMapJson));

    $status = $graphqlQueryMap->save();

    if ($status) {
      drupal_set_message($this->t('Saved the query map version %id.', [
        '%id' => $graphqlQueryMap->id(),
      ]));
    }
    else {
      drupal_set_message($this->t('The query map version %id was not saved.', [
        '%id' => $graphqlQueryMap->id(),
      ]));
    }

    $formState->setRedirect('entity.graphql_query_map.collection');
  }

}
