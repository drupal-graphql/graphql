<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\GraphQL\PluginReferenceInterface;
use Drupal\graphql\GraphQL\PluginReferenceTrait;
use Drupal\graphql\GraphQL\TypeValidationInterface;
use Drupal\graphql\GraphQL\TypeValidationTrait;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Unions\UnionTypePluginBase;
use Youshido\GraphQL\Config\Object\UnionTypeConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Union\AbstractUnionType;

class UnionType extends AbstractUnionType implements PluginReferenceInterface, TypeValidationInterface, CacheableEdgeInterface  {
  use PluginReferenceTrait;
  use CacheableEdgeTrait;
  use TypeValidationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(UnionTypePluginBase $plugin, PluggableSchemaBuilderInterface $builder, array $config = []) {
    $this->plugin = $plugin;
    $this->builder = $builder;
    $this->config = new UnionTypeConfig($config, $this, TRUE);
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
  public function resolveType($object, ResolveInfo $info = NULL) {
    /** @var \Drupal\graphql\GraphQL\Type\ObjectType $type */
    foreach ($this->getTypes() as $type) {
      if ($type->applies($object, $info)) {
        return $type;
      }
    }

    throw new \Exception(sprintf('Could not resolve type for union type %s.', $this->getName()));
  }

  /**
   * {@inheritdoc}
   */
  public function getTypes() {
    return $this->config->get('types', []);
  }

}
