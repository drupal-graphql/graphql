<?php

declare(strict_types = 1);

namespace Drupal\graphql\GraphQL\Response;

use Drupal\file\FileInterface;

/**
 * A response that either has a file entity or some violations.
 */
class FileUploadResponse extends Response {

  /**
   * The file entity in case of successful file upload.
   *
   * @var \Drupal\file\FileInterface|null
   */
  protected $fileEntity;

  /**
   * Sets file entity.
   *
   * @param \Drupal\file\FileInterface $fileEntity
   *   File entity.
   */
  public function setFileEntity(FileInterface $fileEntity): void {
    $this->fileEntity = $fileEntity;
  }

  /**
   * Get the file entity if there is one.
   */
  public function getFileEntity(): ?FileInterface {
    return $this->fileEntity;
  }

}
