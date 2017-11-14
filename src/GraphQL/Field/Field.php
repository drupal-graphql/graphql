<?php

namespace Drupal\graphql\GraphQL\Field;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

class Field extends AbstractField implements SecureFieldInterface, TypeSystemPluginReferenceInterface, CacheableEdgeInterface {
  use TypeSystemPluginReferenceTrait;
  use CacheableEdgeTrait;

  /**
   * Indicates if the field is considered secure.
   *
   * @var bool
   */
  protected $secure;

  /**
   * {@inheritdoc}
   */
  public function __construct(TypeSystemPluginInterface $plugin, $secure, array $config = []) {
    $this->plugin = $plugin;
    $this->secure = $secure;
    $this->config = new FieldConfig($config, $this, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if (($plugin = $this->getPluginFromResolveInfo($info)) && ($plugin instanceof FieldPluginBase || $plugin instanceof MutationPluginBase)) {
      return $plugin->resolve($value, $args, $info);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->getConfigValue('type');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->getConfigValue('name');
  }

  /**
   * {@inheritdoc}
   */
  public function isSecure() {
    return $this->secure;
  }
}
