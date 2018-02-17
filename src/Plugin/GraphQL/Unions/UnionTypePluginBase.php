<?php

namespace Drupal\graphql\Plugin\GraphQL\Unions;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;

abstract class UnionTypePluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilder $builder, TypePluginManager $manager, $definition, $id) {
    return new UnionType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'types' => function () use ($builder, $definition) {
        return array_map(function ($type) use ($builder) {
          if (!(($type = $builder->getTypeByName($type)) instanceof ObjectType)) {
            throw new \LogicException('Union types can only reference object types.');
          }

          return $type;
        }, $definition['types']);
      },
      // TODO: Implement this.
//    'resolveType' => function () use ($builder, $definition) {
//      return $builder->getPossibleTypes($definition['name']);
//    },
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'description' => $this->buildDescription($definition),
      'types' => $this->buildTypes($definition),
    ];
  }

  /**
   * @param $definition
   *
   * @return mixed
   */
  protected function buildTypes($definition) {
    return $definition['types'] ?: [];
  }

}
