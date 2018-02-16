<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use GraphQL\Type\Definition\CustomScalarType;

abstract class ScalarPluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    $callable = ['GraphQL\Type\Definition\Type', strtolower($definition['name'])];
    if (is_callable($callable)) {
      return $callable();
    }

    return new CustomScalarType($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    return $this->getPluginDefinition();
  }

  /**
   * @param mixed $value
   */
  public static function serialize($value) {
    throw new \LogicException('Missing method.');
  }

  /**
   * @param mixed $value
   *
   * @return mixed
   */
  public static function parseValue($value) {
    throw new \LogicException('Missing method.');
  }

  /**
   * @param mixed $node
   *
   * @return mixed
   */
  public static function parseLiteral($node) {
    throw new \LogicException('Missing method.');
  }

}
