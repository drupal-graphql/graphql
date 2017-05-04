<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\PluginTrait;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Config\Object\EnumTypeConfig;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;

/**
 * Base class for graphql field plugins.
 */
abstract class EnumPluginBase extends AbstractEnumType implements GraphQLPluginInterface, CacheableDependencyInterface {
  use PluginTrait;
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  abstract public function buildValues(GraphQLSchemaManagerInterface $schemaManager);

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    $this->config->get('values');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager) {
    $this->config = new EnumTypeConfig([
      'values' => $this->buildValues($schemaManager),
    ]);
  }

}
