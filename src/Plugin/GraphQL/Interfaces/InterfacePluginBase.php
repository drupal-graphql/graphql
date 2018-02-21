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
        $fields = $builder->getFields($definition['name']);

        if (!empty($definition['interfaces'])) {
          $inherited = array_map(function ($name) use ($builder) {
            return $builder->getFields($name);
          }, $definition['interfaces']);

          $inherited = call_user_func_array('array_merge', $inherited);
          return array_merge($inherited, $fields);
        }
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
      'interfaces' => $this->buildInterfaces($definition),
    ];
  }

  /**
   * @param $definition
   *
   * @return mixed
   */
  protected function buildInterfaces($definition) {
    return $definition['interfaces'] ?: [];
  }

}
