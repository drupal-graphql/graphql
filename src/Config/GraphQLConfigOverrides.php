<?php

namespace Drupal\graphql\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * GraphQL config overrides.
 *
 * Enforce the GraphQL language negotiation always to be on top.
 */
class GraphQLConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * The config storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $baseStorage;

  /**
   * GraphQLConfigOverrides constructor.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The config storage service.
   */
  public function __construct(StorageInterface $storage) {
    $this->baseStorage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    if (in_array('language.types', $names)) {
      if ($config = $this->baseStorage->read('language.types')) {
        foreach (array_keys($config['negotiation']) as $type) {
          $config['negotiation'][$type]['enabled']['language-graphql'] = -999;
          asort($config['negotiation'][$type]['enabled']);
        }
        return ['language.types' => $config];
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'graphql';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

}
