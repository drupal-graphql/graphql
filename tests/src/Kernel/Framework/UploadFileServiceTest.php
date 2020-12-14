<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Lock\LockBackendInterface;
use Drupal\graphql\GraphQL\Utility\FileUpload;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Tests file uploads that should be mapped to a field in a resolver.
 *
 * @group graphql
 */
class UploadFileServiceTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['file'];

  /**
   * The FileUpload object we want to test, gets prepared in setUp().
   *
   * @var \Drupal\graphql\GraphQL\Utility\FileUpload
   */
  protected $uploadService;

  /**
   * Path to temporary test file.
   *
   * @var string
   */
  protected $file;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('file');

    $this->uploadService = $this->container->get('graphql.file_upload');

    $file_system = $this->container->get('file_system');
    // Create dummy file, since symfony will test if it exists.
    $this->file = $file_system->getTempDirectory() . '/graphql_upload_test.txt';
    touch($this->file);
  }

  /**
   * Ensure that a correct file upload works.
   */
  public function testSuccess() {
    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK);

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $file_entity = $file_upload_response->getFileEntity();

    $this->assertSame('public://test/test.txt', $file_entity->getFileUri());
    $this->assertFileExists($file_entity->getFileUri());
  }

  /**
   * Tests that a too large file returns a violation.
   */
  public function testFileTooLarge() {
    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_INI_SIZE);

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $violations = $file_upload_response->getViolations();

    $this->assertStringMatchesFormat(
      'The file test.txt could not be saved because it exceeds %d %s, the maximum allowed size for uploads.',
      $violations[0]['message']
    );
  }

  /**
   * Tests that a partial file returns a violation.
   */
  public function testPartialFile() {
    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_PARTIAL);

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $violations = $file_upload_response->getViolations();

    $this->assertStringMatchesFormat(
      'The file "test.txt" could not be saved because the upload did not complete.',
      $violations[0]['message']
    );
  }

  /**
   * Tests that missing settings keys throw an exception.
   */
  public function testMissingSettings() {
    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK);

    $this->expectException(\RuntimeException::class);
    $this->uploadService->saveFileUpload($uploadFile, []);
  }

  /**
   * Tests that the file must not be larger than the file size limit.
   */
  public function testSizeValidation() {
    // Create a file with 4 bytes.
    file_put_contents($this->file, 'test');

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK, 4);

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
      // Only allow 1 byte.
      'max_filesize' => 1,
    ]);
    $violations = $file_upload_response->getViolations();

    // @todo Do we want HTML tags in our violations or not?
    $this->assertStringMatchesFormat(
      'The file is <em class="placeholder">4 bytes</em> exceeding the maximum file size of <em class="placeholder">1 byte</em>.',
      $violations[0]['message']
    );
  }

  /**
   * Tests that the uploaded file extension is renamed to txt.
   */
  public function testExtensionRenaming() {
    // Evil php file extension!
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK, 0, 'test.php');

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $file_entity = $file_upload_response->getFileEntity();

    $this->assertSame('public://test/test.php_.txt', $file_entity->getFileUri());
    $this->assertFileExists($file_entity->getFileUri());
  }

  /**
   * Tests that the uploaded file extension is validated.
   */
  public function testExtensionValidation() {
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK, 0, 'test.txt');

    $file_upload_response = $this->uploadService->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
      // We only allow odt files, so validation must fail.
      'file_extensions' => 'odt',
    ]);
    $violations = $file_upload_response->getViolations();

    // @todo Do we want HTML tags in our violations or not?
    $this->assertStringMatchesFormat(
      'Only files with the following extensions are allowed: <em class="placeholder">odt</em>.',
      $violations[0]['message']
    );
  }

  /**
   * Tests that the file lock is released on validation errors.
   */
  public function testLockReleased() {
    // Mock the lock system to check that the lock is released.
    $lock = $this->prophesize(LockBackendInterface::class);
    $lock->acquire(Argument::any())->willReturn(TRUE);
    // This is our only assertion - that the lock release method is called.
    $lock->release(Argument::any())->shouldBeCalled();

    $upload_service = new FileUpload(
      \Drupal::service('entity_type.manager'),
      \Drupal::service('current_user'),
      \Drupal::service('file.mime_type.guesser'),
      \Drupal::service('file_system'),
      \Drupal::service('logger.channel.graphql'),
      \Drupal::service('token'),
      $lock->reveal(),
      \Drupal::service('config.factory')
    );

    // Create a file with 4 bytes.
    file_put_contents($this->file, 'test');

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = $this->getUploadedFile(UPLOAD_ERR_OK, 4);

    $upload_service->saveFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
      // Only allow 1 byte.
      'max_filesize' => 1,
    ]);
  }

  /**
   * Helper method to prepare the UploadedFile depending on core version.
   *
   * Drupal core uses different Symfony versions where we have a different
   * UploadedFile constructor signature.
   */
  protected function getUploadedFile(
    int $error_status,
    int $size = 0,
    string $name = 'test.txt'
  ): UploadedFile {

    list($version) = explode('.', \Drupal::VERSION, 2);
    switch ($version) {
      case 8:
        return new UploadedFile($this->file, $name, 'text/plain', $size, $error_status, TRUE);

    }
    return new UploadedFile($this->file, $name, 'text/plain', $error_status, TRUE);
  }

}
