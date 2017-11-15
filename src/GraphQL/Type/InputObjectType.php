<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceTrait;
use Youshido\GraphQL\Config\Object\InputObjectTypeConfig;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;

class InputObjectType extends AbstractInputObjectType implements TypeSystemPluginReferenceInterface, CacheableEdgeInterface  {
  use TypeSystemPluginReferenceTrait;
  use CacheableEdgeTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(InputTypePluginBase $plugin, array $config = []) {
    $this->plugin = $plugin;
    $this->config = new InputObjectTypeConfig($config, $this);
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
  public function build($config) {
    // Nothing to do here.
  }
}
