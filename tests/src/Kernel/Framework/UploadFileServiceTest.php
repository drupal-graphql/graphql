<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('file');
  }

  /**
   * Ensure that a correct file upload works.
   */
  public function testSuccess() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    touch($file);

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.txt', 'text/plain', UPLOAD_ERR_OK, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $file_upload_response = $uploadService->createTemporaryFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $file_entity = $file_upload_response->getFileEntity();
    $file_entity->save();

    $this->assertSame('public://test/test.txt', $file_entity->getFileUri());
    $this->assertFileExists($file_entity->getFileUri());
  }

  /**
   * Tests that a too large file returns a violation.
   */
  public function testFileTooLarge() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    touch($file);

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.txt', 'text/plain', UPLOAD_ERR_INI_SIZE, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $file_upload_response = $uploadService->createTemporaryFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $violations = $file_upload_response->getViolations();

    $this->assertStringMatchesFormat(
      'The file test.txt could not be saved because it exceeds %d %s, the maximum allowed size for uploads.',
      $violations[0]
    );
  }

  /**
   * Tests that a partial file returns a violation.
   */
  public function testPartialFile() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    touch($file);

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.txt', 'text/plain', UPLOAD_ERR_PARTIAL, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $file_upload_response = $uploadService->createTemporaryFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $violations = $file_upload_response->getViolations();

    $this->assertStringMatchesFormat(
      'The file "test.txt" could not be saved because the upload did not complete.',
      $violations[0]
    );
  }

  /**
   * Tests that missing settings keys throw an exception.
   */
  public function testMissingSettings() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    touch($file);

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.txt', 'text/plain', UPLOAD_ERR_OK, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $this->expectException(\RuntimeException::class);
    $uploadService->createTemporaryFileUpload($uploadFile, []);
  }

  /**
   * Tests that the file must not be larger than the file size limit.
   */
  public function testSizeValidation() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    // Create a file with 4 bytes.
    file_put_contents($file, 'test');

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.txt', 'text/plain', UPLOAD_ERR_OK, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $file_upload_response = $uploadService->createTemporaryFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
      // Only allow 1 byte.
      'max_filesize' => 1,
    ]);
    $violations = $file_upload_response->getViolations();

    // @todo Do we want HTML tags in our violations or not?
    $this->assertStringMatchesFormat(
      'The file is <em class="placeholder">4 bytes</em> exceeding the maximum file size of <em class="placeholder">1 byte</em>.',
      $violations[0]
    );
  }

  /**
   * Tests that the uploaded file extension is allowed
   */
  public function testExtensionValidation() {
    // Create dummy file, since symfony will test if it exists.
    $file = \Drupal::service('file_system')->getTempDirectory() . '/graphql_upload_test.txt';
    touch($file);

    // Create a Symfony dummy uploaded file in test mode.
    $uploadFile = new UploadedFile($file, 'test.php', 'text/plain', UPLOAD_ERR_OK, TRUE);
    /** @var \Drupal\graphql\GraphQL\Utility\FileUpload */
    $uploadService = \Drupal::service('graphql.file_upload');
    // Activate a test flag to bypass PHP's real file upload validator.
    $uploadService->setInTests(TRUE);

    $file_upload_response = $uploadService->createTemporaryFileUpload($uploadFile, [
      'uri_scheme' => 'public',
      'file_directory' => 'test',
    ]);
    $violations = $file_upload_response->getViolations();

    // @todo Do we want HTML tags in our violations or not?
    $this->assertStringMatchesFormat(
      'Only files with the following extensions are allowed: <em class="placeholder">txt</em>.',
      $violations[0]
    );
  }

}
