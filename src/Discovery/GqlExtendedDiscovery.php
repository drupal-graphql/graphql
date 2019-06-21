<?php

namespace Drupal\graphql\Discovery;

/**
 * Provides discovery for GQL files within a given set of directories.
 *
 * Copied from YamlDiscovery.
 */
class GqlExtendedDiscovery extends GqlDiscovery {

  /**
   * File cache collection name for extended schema files.
   *
   * @var string
   */
  protected $collection = 'gql_extended_discovery';

  /**
   * Returns an array of file paths, keyed by provider.
   *
   * @return array
   *   List of graphql files.
   */
  protected function findFiles() {
    $files = [];
    foreach ($this->directories as $provider => $directory) {
      $file = $directory . '/' . $provider . '.extend.gql';
      if (file_exists($file)) {
        $files[$provider] = $file;
      }
    }
    return $files;
  }

}
