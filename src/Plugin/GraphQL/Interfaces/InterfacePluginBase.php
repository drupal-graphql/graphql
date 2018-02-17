<?php

namespace Drupal\graphql\Plugin\GraphQL\Interfaces;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\InterfaceType;

abstract class InterfacePluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilder $builder, TypePluginManager $manager, $definition, $id) {
    return new InterfaceType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'fields' => function () use ($builder, $definition) {
        return $builder->getFields($definition['name']);
      },
      // TODO: Implement this.
//      'resolveType' => function () use ($builder, $definition) {
//        return $builder->getPossibleTypes($definition['name']);
//      },
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
    ];
  }

}
