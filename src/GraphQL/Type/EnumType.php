<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\GraphQL\PluginReferenceInterface;
use Drupal\graphql\GraphQL\PluginReferenceTrait;
use Drupal\graphql\GraphQL\TypeValidationInterface;
use Drupal\graphql\GraphQL\TypeValidationTrait;
use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Config\Object\EnumTypeConfig;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;

class EnumType extends AbstractEnumType implements PluginReferenceInterface, TypeValidationInterface, CacheableEdgeInterface {
  use PluginReferenceTrait;
  use CacheableEdgeTrait;
  use TypeValidationTrait;

  /**
   * EnumType constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase $plugin
   *   The enum plugin instance.
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $builder
   *   The schema builder.
   * @param array $config
   *   The enum config array.
   */
  public function __construct(EnumPluginBase $plugin, PluggableSchemaBuilderInterface $builder, array $config = []) {
    $this->plugin = $plugin;
    $this->builder = $builder;
    $this->config = new EnumTypeConfig($config, $this);
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigValue($key, $default = NULL) {
    return !empty($this->config) ? $this->config->get($key, $default) : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->getConfigValue('values', []);
  }
}
