<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceTrait;
use Youshido\GraphQL\Config\Object\EnumTypeConfig;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;

class EnumType extends AbstractEnumType implements TypeSystemPluginReferenceInterface, CacheableEdgeInterface {
  use TypeSystemPluginReferenceTrait;
  use CacheableEdgeTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(EnumPluginBase $plugin, array $config = []) {
    $this->plugin = $plugin;
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
