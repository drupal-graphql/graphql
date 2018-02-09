<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class EntityCrudOutputWrapper {
  /**
   * The create entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface|NULL
   */
  protected $entity;

  /**
   * The constraint validation list.
   *
   * @var \Symfony\Component\Validator\ConstraintViolationListInterface|NULL
   */
  protected $violations;

  /**
   * An array of error messages.
   *
   * @var array|NULL
   */
  protected $errors;

  /**
   * Creates a CreateEntityOutputWrapper object.
   *
   * @param \Drupal\Core\Entity\EntityInterface|NULL $entity
   *   The entity object that has been created or NULL if creation failed.
   * @param \Symfony\Component\Validator\ConstraintViolationListInterface|NULL $violations
   *   The validation errors that occurred during creation or NULL if validation
   *   succeeded.
   * @param array|NULL $errors
   *   An array of non validation error messages. Can be used to provide
   *   additional error messages e.g. for access restrictions.
   */
  public function __construct(
    EntityInterface $entity = NULL,
    ConstraintViolationListInterface $violations = NULL,
    array $errors = NULL
  ) {
    $this->entity = $entity;
    $this->violations = $violations;
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
   * Returns the constraint violations.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface|NULL
   *   The constraint validations or NULL if validation passed.
   */
  public function getViolations() {
    return $this->violations;
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
