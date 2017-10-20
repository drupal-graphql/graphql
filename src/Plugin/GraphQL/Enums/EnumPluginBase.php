<?php

namespace Drupal\graphql\Plugin\GraphQL\Enums;

use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Object\EnumTypeConfig;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;

/**
 * Base class for graphql field plugins.
 */
abstract class EnumPluginBase extends AbstractEnumType implements TypeSystemPluginInterface {
  use PluginTrait;
  use NamedPluginTrait;
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  abstract public function buildValues(SchemaBuilderInterface $schemaManager);

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->config->get('values');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
    $this->config = new EnumTypeConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'values' => $this->buildValues($schemaManager),
    ]);
  }

}
