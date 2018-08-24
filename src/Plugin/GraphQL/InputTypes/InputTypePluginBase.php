<?php

namespace Drupal\graphql\Plugin\GraphQL\InputTypes;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use Drupal\graphql\Utility\StringHelper;
use GraphQL\Type\Definition\InputObjectType;

abstract class InputTypePluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;
  use TypedPluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    return new InputObjectType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'contexts' => $definition['contexts'],
      'fields' => function () use ($builder, $definition) {
        return $builder->processArguments($definition['fields']);
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
      'fields' => $this->buildFields($definition),
      'contexts' => $this->buildCacheContexts($definition),
    ];
  }

  /**
   * Builds the fields of the type definition.
   *
   * @param $definition
   *   The plugin definition array.
   *
   * @return array
   *   The list of fields for the input type.
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
   * Builds a field's type.
   *
   * @param array $field
   *   The field definition array.
   *
   * @return array
   *   The parsed type definition array.
   */
  protected function buildFieldType($field) {
    $type = is_array($field) ? $field['type'] : $field;
    return StringHelper::parseType($type);
  }

  /**
   * Builds a field's description.
   *
   * @param array $field
   *   The field definition array.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return string
   *   The field's description.
   */
  protected function buildFieldDescription($field, $definition) {
    return (string) (isset($field['description']) ? $field['description'] : '');
  }

  /**
   * Builds a field's default value.
   *
   * @param array $field
   *   The field definition array.
   * @param array $definition
   *   The plugin definition array.
   *
   * @return mixed
   *   The field's default value.
   */
  protected function buildFieldDefault($field, $definition) {
    return isset($field['default']) ? $field['default'] : NULL;
  }
}
