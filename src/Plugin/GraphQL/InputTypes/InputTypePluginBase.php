<?php

namespace Drupal\graphql\Plugin\GraphQL\InputTypes;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Utility\StringHelper;
use GraphQL\Type\Definition\InputObjectType;

abstract class InputTypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;
  use TypedPluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return new InputObjectType([
      'fields' => function () use ($builder, $definition) {
        return $builder->resolveArgs($definition['fields']);
      },
    ] + $definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'name' => $definition['name'],
      'description' => $this->buildDescription($definition),
      'fields' => $this->buildFields($definition),
    ];
  }

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildFields($definition) {
    return array_map(function ($field) use ($definition) {
      return [
        'type' => $this->buildFieldType($field, $definition),
        'description' => $this->buildFieldDescription($field, $definition),
        'default' => $this->buildFieldDefault($field, $definition),
      ];
    }, $definition['fields']);
  }

  /**
   * @param $field
   *
   * @return array
   */
  protected function buildFieldType($field) {
    $type = is_array($field) ? $field['type'] : $field;
    return StringHelper::parseType($type);
  }

  /**
   * @param $field
   * @param $definition
   *
   * @return string
   */
  protected function buildFieldDescription($field, $definition) {
    return (string) (isset($field['description']) ? $field['description'] : '');
  }

  /**
   * @param $field
   * @param $definition
   *
   * @return null
   */
  protected function buildFieldDefault($field, $definition) {
    return isset($field['default']) ? $field['default'] : NULL;
  }
}
