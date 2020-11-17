<?php

namespace Drupal\graphql\GraphQL\Utility;

use Drupal\Component\Utility\Bytes;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Response\FileUploadResponse;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service to manage file uploads within GraphQL mutations.
 *
 * This service handles file validations like max upload size.
 */
class FileUpload {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The mime type guesser service.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * GraphQL logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxyInterface $currentUser,
    MimeTypeGuesserInterface $mimeTypeGuesser,
    FileSystemInterface $fileSystem,
    LoggerChannelInterface $logger
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
    $this->fileSystem = $fileSystem;
    $this->logger = $logger;
  }

  /**
   * Gets max upload size.
   *
   * @param array $settings
   *   The file field settings.
   *
   * @return int
   *   Max upload size.
   */
  protected function getMaxUploadSize(array $settings) {
    // Cap the upload size according to the PHP limit.
    $max_filesize = Bytes::toInt(Environment::getUploadMaxSize());
    if (!empty($settings['max_filesize'])) {
      $max_filesize = min($max_filesize, Bytes::toInt($settings['max_filesize']));
    }
    return $max_filesize;
  }

  /**
   * Retrieves the upload validators for a file field.
   *
   * @param array $settings
   *   The file field settings.
   *
   * @return array
   *   List of file validators.
   */
  protected function getUploadValidators(array $settings) {
    // Validate name length.
    $validators = [
      'file_validate_name_length' => [],
    ];

    // There is always a file size limit due to the PHP server limit.
    $validators['file_validate_size'] = [$this->getMaxUploadSize($settings)];

    // Add the extension check if necessary.
    if (empty($settings['file_extensions'])) {
      $validators['file_validate_extensions'] = ['txt'];
    }
    else {
      $validators['file_validate_extensions'] = [$settings['file_extensions']];
    }

    return $validators;
  }

  /**
   * Create a temporary file and send back the newly created entity.
   *
   * Based on several file upload handlers, see
   * _file_save_upload_single()
   * \Drupal\file\Plugin\Field\FieldType\FileItem
   * \Drupal\file\Plugin\rest\resource\FileUploadResource.
   *
   * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
   *   The file entity to upload.
   * @param array $settings
   *   File settings as specified in regular file field config. Contains keys:
   *   - file_directory: Where to upload the file
   *   - uri_scheme: Uri scheme to upload the file to (eg public://, private://)
   *   - file_extensions: List of valid file extensions (eg [xml, pdf])
   *   - max_filesize: Maximum allowed size of uploaded file.
   *
   * @return \Drupal\graphql\GraphQL\Response\FileUploadResponse
   *   The file upload response containing file entity or list of violations.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \RuntimeException
   */
  public function createTemporaryFileUpload(UploadedFile $file, array $settings): FileUploadResponse {
    $response = new FileUploadResponse();

    // Check for file upload errors and return FALSE for this file if a lower
    // level system error occurred.
    // @see http://php.net/manual/features.file-upload.errors.php.
    switch ($file->getError()) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $maxUploadSize = format_size($this->getMaxUploadSize($settings));
        $response->addViolation($this->t('The file @file could not be saved because it exceeds @maxsize, the maximum allowed size for uploads.', [
          '@file' => $file->getClientOriginalName(),
          '@maxsize' => $maxUploadSize,
        ]));
        return $response;

      case UPLOAD_ERR_PARTIAL:
      case UPLOAD_ERR_NO_FILE:
        $response->addViolation($this->t('The file "@file" could not be saved because the upload did not complete.', [
          '@file' => $file->getClientOriginalName(),
        ]));
        return $response;

      case UPLOAD_ERR_OK:
        // Final check that this is a valid upload, if it isn't, use the
        // default error handler.
        if ($file->isValid()) {
          break;
        }

      default:
        $response->addViolation($this->t('Unknown error while uploading the file "@file".', ['@file' => $file->getClientOriginalName()]));
        $this->logger->error('Error while uploading the file "@file" with an error code "@code".', [
          '@file' => $file->getFilename(),
          '@code' => $file->getError(),
        ]);
        return $response;
    }

    if (empty($settings['uri_scheme']) || empty($settings['file_directory'])) {
      throw new \RuntimeException('uri_scheme or file_directory missing in settings');
    }

    // Make sure the destination directory exists.
    $uploadDir = $settings['uri_scheme'] . '://' . trim($settings['file_directory'], '/');
    if (!$this->fileSystem->prepareDirectory($uploadDir, FileSystem::CREATE_DIRECTORY)) {
      $response->addViolation($this->t('Unknown error while uploading the file "@file".', ['@file' => $file->getClientOriginalName()]));
      $this->logger->error('Could not create directory "@upload_directory".', ["@upload_directory" => $uploadDir]);
      return $response;
    }
    $name = $file->getClientOriginalName();
    $mime = $this->mimeTypeGuesser->guess($name);
    $destination = $this->fileSystem->getDestinationFilename("{$uploadDir}/{$name}", $this->fileSystem::EXISTS_RENAME);

    // Begin building file entity.
    $values = [
      'uid' => $this->currentUser->id(),
      'status' => 0,
      'filename' => $name,
      'uri' => $destination,
      'filesize' => $file->getSize(),
      'filemime' => $mime,
    ];
    $storage = $this->entityTypeManager->getStorage('file');
    /** @var \Drupal\file\FileInterface $fileEntity */
    $fileEntity = $storage->create($values);

    // Validate the entity values.
    $violations = $fileEntity->validate();
    if ($violations->count()) {
      foreach ($violations as $violation) {
        $response->addViolation($violation->getMessage());
      }
      return $response;
    }

    // Validate the file name length.
    if ($violations = file_validate($fileEntity, $this->getUploadValidators($settings))) {
      $response->addViolations($violations);
      return $response;
    }

    // Move uploaded files from PHP's upload_tmp_dir to Drupal's temporary
    // directory. This overcomes open_basedir restrictions for future file
    // operations.
    if (!$this->fileSystem->moveUploadedFile($file->getRealPath(), $fileEntity->getFileUri())) {
      $response->addViolation($this->t('Unknown error while uploading the file "@file".', [
        '@file' => $file->getClientOriginalName(),
      ]));
      $this->logger->error('Unable to move file from "@file" to "@destination".', [
        '@file' => $file->getRealPath(),
        '@destination' => $fileEntity->getFileUri(),
      ]);
      return $response;
    }

    // Adjust permissions.
    if (!$this->fileSystem->chmod($fileEntity->getFileUri())) {
      $response->addViolation($this->t('Unknown error while uploading the file "@file".', ['@file' => $file->getClientOriginalName()]));
      $this->logger->error('Unable to set file permission for file "@file".', ['@file' => $fileEntity->getFileUri()]);
      return $response;
    }

    $response->setFileEntity($fileEntity);
    return $response;
  }

}
