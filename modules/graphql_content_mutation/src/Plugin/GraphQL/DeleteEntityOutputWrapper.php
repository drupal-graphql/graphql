<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL;

use Drupal\Core\Entity\EntityInterface;

class DeleteEntityOutputWrapper {
  /**
   * The deleted entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface|NULL
   */
  protected $entity;

  /**
   * An array of error messages.
   *
   * @var array|NULL
   */
  protected $errors;

  /**
   * Creates a DeleteEntityOutputWrapper object.
   *
   * @param \Drupal\Core\Entity\EntityInterface|NULL $entity
   *   The entity object that has been created or NULL if creation failed.
   * @param array|NULL $errors
   *   An array of non validation error messages. Can be used to provide
   *   additional error messages e.g. for access restrictions.
   */
  public function __construct(
    EntityInterface $entity = NULL,
    array $errors = NULL
  ) {
    $this->entity = $entity;
    $this->errors = $errors;
  }

  /**
   * Returns the entity that was created.
   *
   * @return \Drupal\Core\Entity\EntityInterface|NULL
   *   The created entity object or NULL if creation failed.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Returns a list of error messages that occurred during entity creation.
   *
   * @return array|NULL
   *   An array of error messages as plain strings.
   */
  public function getErrors() {
    return $this->errors;
  }

}
