<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\CustomScalarType;

abstract class ScalarPluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    $callable = ['GraphQL\Type\Definition\Type', strtolower($definition['name'])];
    if (is_callable($callable)) {
      return $callable();
    }

    $class = get_called_class();
    return new CustomScalarType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'contexts' => $definition['contexts'],
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
      'contexts' => $this->buildCacheContexts($definition),
    ];
  }

  /**
   * Serializes the scalar value.
   *
   * @param mixed $value
   *   The value to serialize.
   */
  public static function serialize($value) {
    throw new \LogicException('Missing method.');
  }

  /**
   * Parses a value.
   *
   * @param mixed $value
   *   The value to parse.
   *
   * @return mixed
   *   The parsed value.
   */
  public static function parseValue($value) {
    throw new \LogicException('Missing method.');
  }

  /**
   * Parses a node.
   *
   * @param mixed $node
   *   The node to parse.
   *
   * @return mixed
   *   The parsed node.
   */
  public static function parseLiteral($node) {
    throw new \LogicException('Missing method.');
  }

}
