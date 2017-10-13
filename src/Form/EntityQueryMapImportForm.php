<?php

namespace Drupal\graphql\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Entity\QueryMap;

/**
 * Form controller for GraphQL query map forms.
 */
class EntityQueryMapImportForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $files = file_save_upload('query_map_json', [
      'file_validate_extensions' => ['json'], // Validate extensions.
    ]);

    /** @var \Drupal\file\FileInterface $file */
    if (empty($files) || !($file = reset($files)) || !$file instanceof FileInterface) {
      $form_state->setError($form['query_map_json'], $this->t('No file was uploaded.'));
    }
    else {
      // Save the file for use in the submit handler.
      $form_state->set('file', $file);
      $queryMapJson = file_get_contents($file->getFileUri());
      $version = sha1($queryMapJson);

      if (QueryMap::exists($version)) {
        $form_state->setError($form['query_map_json'], $this->t('A query map with the same version @version already exists.', ['@version' => $version]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $file = $form_state->get('file');
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

    $form_state->setRedirect('entity.graphql_query_map.collection');
  }

}
