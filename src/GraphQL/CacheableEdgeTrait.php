<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Schema\Schema;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Schema\AbstractSchema;

trait CacheableEdgeTrait {

  /**
   * {@inheritdoc}
   */
  abstract public function getPlugin(PluggableSchemaBuilderInterface $schemaBuilder);

  /**
   * {@inheritdoc}
   */
  public function getSchemaCacheMetadata(AbstractSchema $schema) {
    return $this->getCacheMetadata($schema, function (TypeSystemPluginInterface $plugin) {
      return $plugin->getSchemaCacheMetadata();
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseCacheMetadata(AbstractSchema $schema) {
    return $this->getCacheMetadata($schema, function (TypeSystemPluginInterface $plugin) {
      return $plugin->getResponseCacheMetadata();
    });
  }

  /**
   * Helper function to load cache metadata from a schema's edges.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema.
   * @param callable $callback
   *   Callback to return the cache metadata from an edge.
   *
   * @return \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   *   The cache metadata.
   */
  protected function getCacheMetadata(AbstractSchema $schema, callable $callback) {
    if (!$schema instanceof Schema) {
      return NULL;
    }

    $schemaPlugin = $schema->getSchemaPlugin();
    if (!$schemaPlugin instanceof PluggableSchemaPluginInterface) {
      return NULL;
    }

    /** @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface $plugin */
    if ($plugin = $this->getPlugin($schemaPlugin->getSchemaBuilder())) {
      return $callback($plugin);
    }

    return NULL;
  }

}