<?php

namespace Drupal\graphql\Plugin\GraphQL\Interfaces;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\InterfaceType;

abstract class InterfacePluginBase extends PluginBase implements TypePluginInterface {
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
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

        return $fields;
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
   * Builds the list of interfaces inherited by this interface.
   *
   * @param array $definition
   *   The plugin definition array.
   *
   * @return array
   *   The list of interfaces that this interface inherits from.
   */
  protected function buildInterfaces($definition) {
    return $definition['interfaces'] ?: [];
  }

}
