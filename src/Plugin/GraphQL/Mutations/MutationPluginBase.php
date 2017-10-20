<?php

namespace Drupal\graphql\Plugin\GraphQL\Mutations;

use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Field\AbstractField;

/**
 * Base class for graphql mutation plugins.
 */
abstract class MutationPluginBase extends AbstractField implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use ArgumentAwarePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
    $this->config = new FieldConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'type' => $this->buildType($schemaManager),
      'args' => $this->buildArguments($schemaManager),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    // May be overridden, but not required any more.
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->config->getType();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->buildName();
  }

}
