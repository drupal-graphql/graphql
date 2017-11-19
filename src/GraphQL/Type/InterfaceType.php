<?php

namespace Drupal\graphql\GraphQL\Type;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\CacheableEdgeTrait;
use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceTrait;
use Youshido\GraphQL\Config\Object\InterfaceTypeConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class InterfaceType extends AbstractInterfaceType implements TypeSystemPluginReferenceInterface, CacheableEdgeInterface {
  use TypeSystemPluginReferenceTrait;
  use CacheableEdgeTrait;

  /**
   * List of types implementing this interface.
   *
   * @var \Drupal\graphql\GraphQL\Type\ObjectType[]
   */
  protected $types;

  /**
   * {@inheritdoc}
   */
  public function __construct(InterfacePluginBase $plugin, array $config = []) {
    $this->plugin = $plugin;
    $this->config = new InterfaceTypeConfig($config, $this, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigValue($key, $default = NULL) {
    return !empty($this->config) ? $this->config->get($key, $default) : $default;
  }

  /**
   * Registers a type that implements this interface.
   *
   * @param \Drupal\graphql\GraphQL\Type\ObjectType $type
   *   The type to register on this interface.
   * @param int $weight
   *   The weight of the type.
   */
  public function registerType(ObjectType $type, $weight = 0) {
    $this->types[$type->getName()] = [
      'weight' => $weight,
      'type' => $type,
    ];

    uasort($this->types, function (array $a, array $b) {
      if ($a['weight'] === $b['weight']) {
        return 0;
      }

      return ($a['weight'] < $b['weight']) ? 1 : -1;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object, ResolveInfo $info = NULL) {
    /** @var \Drupal\graphql\GraphQL\Type\ObjectType $type */
    foreach ($this->types as $item) {
      $type = $item['type'];
      if ($type->applies($object, $info)) {
        return $type;
      }
    }

    throw new \Exception(sprintf('Could not resolve type for interface %s.', $this->getName()));
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // Nothing to do here.
  }

}
