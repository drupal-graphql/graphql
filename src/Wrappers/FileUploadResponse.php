<?php

namespace Drupal\graphql\Wrappers;

use Drupal\file\FileInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * File upload response wrapper.
 */
class FileUploadResponse implements FileUploadResponseInterface {

  /**
   * List of violations in case of unsuccessful file upload.
   *
   * @var array
   */
  protected $violations = [];

  /**
   * The file entity in case of successful file upload.
   *
   * @var \Drupal\file\FileInterface|null
   */
  protected $fileEntity;

  /**
   * Sets violation.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup|\Symfony\Component\Validator\ConstraintViolationInterface $violation
   *   Violation. Either string, translatable markup or constraint.
   */
  public function setViolation($violation) {
    if ($violation instanceof ConstraintViolationInterface) {
      $violation = $violation->getMessage();
    }
    $this->violations[] = (string) $violation;
  }

  /**
   * Sets violations.
   *
   * @param array|\Symfony\Component\Validator\ConstraintViolationListInterface $violations
   *   List of violations.
   */
  public function setViolations($violations) {
    foreach ($violations as $violation) {
      $this->setViolation($violation);
    }
  }

  /**
   * Sets file entity.
   *
   * @param \Drupal\file\FileInterface $fileEntity
   *   File entity.
   */
  public function setFileEntity(FileInterface $fileEntity) {
    $this->fileEntity = $fileEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function getViolations() {
    return $this->violations;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileEntity() {
    return $this->fileEntity;
  }

}
