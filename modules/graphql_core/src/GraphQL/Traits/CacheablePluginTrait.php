<?php

namespace Drupal\graphql_core\GraphQL\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Cache\Cache;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Methods for GraphQL plugins that are cacheable.
 */
trait CacheablePluginTrait {

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_contexts']) ? $definition['cache_contexts'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_tags']) ? $definition['cache_tags'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_max_age']) ? $definition['cache_max_age'] : Cache::PERMANENT;
  }
}
