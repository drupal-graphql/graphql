<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceTrait;
use Drupal\graphql\Plugin\GraphQL\Unions\UnionTypePluginBase;
use Youshido\GraphQL\Config\Object\UnionTypeConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Union\AbstractUnionType;

class UnionType extends AbstractUnionType implements TypeSystemPluginReferenceInterface, CacheableEdgeInterface  {
  use TypeSystemPluginReferenceTrait;
  use CacheableEdgeTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(UnionTypePluginBase $plugin, array $config = []) {
    $this->plugin = $plugin;
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
