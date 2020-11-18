<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\File\FileSystem;

/**
 * Helper class to mock the moveUploadedFile() method during testing.
 *
 * @internal
 */
class MockFileSystem extends FileSystem {

  /**
   * The file system service used to proxy to.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  private $fileSystem;

  /**
   * Constructs this mock with the file system service used to proxy.
   */
  public function __construct(FileSystem $file_system) {
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDirectory(&$directory, $options = self::MODIFY_PERMISSIONS) {
    return $this->fileSystem->prepareDirectory($directory, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function moveUploadedFile($filename, $uri) {
    // We can use the normal move() functionality instead during testing.
    $this->fileSystem->move($filename, $uri);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function chmod($uri, $mode = NULL) {
    return $this->fileSystem->chmod($uri, $mode);
  }

}
