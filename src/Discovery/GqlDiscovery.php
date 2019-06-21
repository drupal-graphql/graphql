<?php

namespace Drupal\graphql\Discovery;

use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\Discovery\DiscoverableInterface;

/**
 * Provides discovery for GQL files within a given set of directories.
 *
 * Copied from YamlDiscovery.
 */
class GqlDiscovery implements DiscoverableInterface {

  /**
   * File cache collection name for base schema files.
   *
   * @var string
   */
  protected $collection = 'gql_discovery';

  /**
   * An array of directories to scan, keyed by the provider.
   *
   * @var array
   */
  protected $directories = [];

  /**
   * Constructor.
   *
   * @param array $directories
   *   An array of directories to scan, keyed by the provider.
   */
  public function __construct(array $directories) {
    $this->directories = $directories;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $all = [];

    $files = $this->findFiles();
    $provider_by_files = array_flip($files);

    $file_cache = FileCacheFactory::get($this->collection);

    // Try to load from the file cache first.
    foreach ($file_cache->getMultiple($files) as $file => $data) {
      $all[$provider_by_files[$file]] = $data;
      unset($provider_by_files[$file]);
    }

    // If there are files left that were not returned from the cache, load and
    // parse them now. This list was flipped above and is keyed by filename.
    if ($provider_by_files) {
      foreach ($provider_by_files as $file => $provider) {
        $file_content = file_get_contents($file);
        $all[$provider] = $file_content ?: '';
        $file_cache->set($file, $all[$provider]);
      }
    }

    return $all;
  }

  /**
   * Returns an array of file paths, keyed by provider.
   *
   * @return array
   *   List of graphql files.
   */
  protected function findFiles() {
    $files = [];
    foreach ($this->directories as $provider => $directory) {
      $file = $directory . '/' . $provider . '.gql';
      if (file_exists($file)) {
        $files[$provider] = $file;
      }
    }
    return $files;
  }

}
