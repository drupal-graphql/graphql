<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\GraphQL\PluginReferenceInterface;
use Drupal\graphql\GraphQL\PluginReferenceTrait;
use Drupal\graphql\GraphQL\TypeValidationInterface;
use Drupal\graphql\GraphQL\TypeValidationTrait;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class ObjectType extends AbstractObjectType implements PluginReferenceInterface, TypeValidationInterface, CacheableEdgeInterface  {
  use PluginReferenceTrait;
  use CacheableEdgeTrait;
  use TypeValidationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(TypePluginBase $plugin, PluggableSchemaBuilderInterface $builder, array $config = []) {
    $this->plugin = $plugin;
    $this->builder = $builder;
    $this->config = new ObjectTypeConfig($config, $this, TRUE);
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
  public function getName() {
    return $this->getConfigValue('name');
  }

  /**
   * Checks whether this type applies to a given object.
   *
   * @param mixed $object
   *   The object to check against.
   * @param \Youshido\GraphQL\Execution\ResolveInfo|null $info
   *   The resolve info object.
   *
   * @return bool
   *   TRUE if this type applies to the given object, FALSE otherwise.
   */
  public function applies($object, ResolveInfo $info = NULL) {
    if (($plugin = $this->getPlugin()) && $plugin instanceof TypePluginBase) {
      return $plugin->applies($object, $info);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // Nothing to do here.
  }

}
