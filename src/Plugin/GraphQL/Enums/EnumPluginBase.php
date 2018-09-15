<?php

namespace Drupal\graphql\Plugin\GraphQL\Enums;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\EnumType;

abstract class EnumPluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    return new EnumType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'values' => $definition['values'],
      'contexts' => $definition['contexts'],
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
      'values' => $this->buildEnumValues($definition),
      'contexts' => $this->buildCacheContexts($definition),
    ];
  }

  /**
   * Builds the enum values.
   *
   * @param array $definition
   *   The plugin definition array/
   *
   * @return array
   *   The enum values.
   */
  protected function buildEnumValues($definition) {
    return array_map(function ($value) use ($definition) {
      return [
        'value' => $this->buildEnumValue($value, $definition),
        'description' => $this->buildEnumDescription($value, $definition),
      ];
    }, $definition['values']);
  }

  /**
   * Builds the value of an enum item.
   *
   * @param mixed $value
   *   The enum's value definition.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return mixed
   *   The value of the enum item.
   */
  protected function buildEnumValue($value, $definition) {
    return is_array($value) ? $value['value'] : $value;
  }

  /**
   * Builds the description of an enum item.
   *
   * @param mixed $value
   *   The enum's value definition.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return string
   *   The description of the enum item.
   */
  protected function buildEnumDescription($value, $definition) {
    return (string) (is_array($value) ? $value['description'] : '');
  }

}
