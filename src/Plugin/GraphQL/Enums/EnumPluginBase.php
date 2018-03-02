<?php

namespace Drupal\graphql\Plugin\GraphQL\Enums;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\EnumType;

abstract class EnumPluginBase extends PluginBase implements TypePluginInterface {
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilder $builder, TypePluginManager $manager, $definition, $id) {
    return new EnumType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'values' => $definition['values'],
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
    ];
  }

  /**
   * @param $definition
   *
   * @return array
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
   * @param $value
   * @param $definition
   *
   * @return mixed
   */
  protected function buildEnumValue($value, $definition) {
    return is_array($value) ? $value['value'] : $value;
  }

  /**
   * @param $value
   * @param $definition
   *
   * @return string
   */
  protected function buildEnumDescription($value, $definition) {
    return (string) (is_array($value) ? $value['description'] : '');
  }

}
