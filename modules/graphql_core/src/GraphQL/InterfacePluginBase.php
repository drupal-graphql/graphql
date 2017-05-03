<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql_core\GraphQL\Traits\PluginTrait;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Config\Object\InterfaceTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

/**
 * Base class for GraphQL interface plugins.
 */
abstract class InterfacePluginBase extends AbstractInterfaceType implements GraphQLPluginInterface, CacheableDependencyInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager) {
    $this->config = new InterfaceTypeConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'fields' => $this->buildFields($schemaManager),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // May be overridden, but not required any more.
  }

}
