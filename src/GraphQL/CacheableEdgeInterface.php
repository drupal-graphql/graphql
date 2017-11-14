<?php

namespace Drupal\graphql\GraphQL;

use Youshido\GraphQL\Schema\AbstractSchema;

interface CacheableEdgeInterface {

  /**
   * Returns the cache metadata affecting the schema.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema that this edge belongs to.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata affecting the schema.
   */
  public function getSchemaCacheMetadata(AbstractSchema $schema);

  /**
   * Returns the cache metadata affecting the response.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema that this edge belongs to.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata affecting the response.
   */
  public function getResponseCacheMetadata(AbstractSchema $schema);


}