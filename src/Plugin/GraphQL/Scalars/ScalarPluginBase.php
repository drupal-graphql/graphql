<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\CustomScalarType;

abstract class ScalarPluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilder $builder, TypePluginManager $manager, $definition, $id) {
    $callable = ['GraphQL\Type\Definition\Type', strtolower($definition['name'])];
    if (is_callable($callable)) {
      return $callable();
    }

    $class = get_called_class();
    return new CustomScalarType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'serialize' => [$class, 'serialize'],
      'parseValue' => [$class, 'parseValue'],
      'parseLiteral' => [$class, 'parseLiteral'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'name' => $definition['name'],
      'description' => $this->buildDescription($definition),
    ] + $this->buildCacheMetadata($definition);
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
