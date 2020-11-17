<?php

namespace Drupal\graphql\Wrappers;

/**
 * Defines interface for file upload responses.
 */
interface FileUploadResponseInterface {

  /**
   * Gets violations.
   *
   * @return array
   *   List of violations.
   */
  public function getViolations();

  /**
   * Gets file entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   File entity.
   */
  public function getFileEntity();

}
