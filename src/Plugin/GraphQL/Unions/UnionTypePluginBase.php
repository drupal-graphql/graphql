<?php

namespace Drupal\graphql\Plugin\GraphQL\Unions;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;

abstract class UnionTypePluginBase extends PluginBase implements TypePluginInterface {
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    return new UnionType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'types' => function () use ($builder, $definition) {
        return array_map(function ($type) use ($builder) {
          if (!(($type = $builder->getType($type)) instanceof ObjectType)) {
            throw new \LogicException('Union types can only reference object types.');
          }

          return $type;
        }, $builder->getSubTypes($definition['name']));
      },
      'resolveType' => function ($value, $context, $info) use ($builder, $definition) {
        return $builder->resolveType($definition['name'], $value, $context, $info);
      },
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
      'types' => $this->buildTypes($definition),
    ];
  }

  /**
   * Builds the list of types contained within this union type.
   *
   * @param array $definition
   *   The plugin definion array.
   *
   * @return array
   *   The list of types contained within this union type.
   */
  protected function buildTypes($definition) {
    return $definition['types'] ?: [];
  }
}
