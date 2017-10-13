<?php

namespace Drupal\graphql\QueryMapProvider;

use Drupal\Component\FileSystem\RegexDirectoryIterator;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class JsonQueryMapProvider implements QueryMapProviderInterface {
  /**
   * The cache backend for storing query map file paths.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The paths to use for finding query maps.
   *
   * @var string[]
   */
  protected $lookupPaths;

  /**
   * Constructs a QueryMapProvider object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for storing query map file paths.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(CacheBackendInterface $cacheBackend, ConfigFactoryInterface $configFactory) {
    $this->lookupPaths = $configFactory->get('graphql.query_map_json.config')->get('lookup_paths');
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($version, $id) {
    if (!(($cache = $this->cacheBackend->get('graphql_query_map_json_versions')) && ($versions = $cache->data) !== NULL)) {
      $this->cacheBackend->set('graphql_query_map_json_versions', $versions = $this->discoverQueryMaps());
    }

    if (isset($versions[$version]) && file_exists($versions[$version])) {
      $contents = json_decode(file_get_contents($versions[$version]), TRUE);
      if ($query = array_search($id, $contents)) {
        return $query;
      }
    }

    return NULL;
  }

  /**
   * Discovers the available query maps within the configured lookup paths.
   *
   * @return array
   *   An associative array of query maps with the query map versions as keys.
   */
  protected function discoverQueryMaps() {
    $maps = [];
    foreach ($this->lookupPaths as $path) {
      if (is_dir($path)) {
        $iterator = new RegexDirectoryIterator($path, '/\.json/i');

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
          $hash = sha1(file_get_contents($file->getPathname()));
          $maps[$hash] = $file->getPathname();
        }
      }

      if (is_file($path)) {
        $file = new \SplFileInfo($path);
        $hash = sha1(file_get_contents($file->getPathname()));
        $maps[$hash] = $file->getPathname();
      }
    }

    return $maps;
  }
}
