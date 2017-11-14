<?php

namespace Drupal\graphql\Plugin\GraphQL\InputTypes;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\GraphQL\Type\InputObjectType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Object\InputObjectTypeConfig;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Base class for input type plugins.
 */
abstract class InputTypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use TypedPluginTrait;

  /**
   * The type instance.
   *
   * @var \Drupal\graphql\GraphQL\Type\InputObjectType
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new InputObjectType($this, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
        'fields' => $this->buildFields($schemaBuilder),
      ]);
    }

    return $this->definition;
  }

  /**
   * Build the field list.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\FieldInterface[]
   *   The list of fields.
   */
  protected function buildFields(PluggableSchemaBuilderInterface $schemaBuilder) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (!$definition['fields']) {
        return [];
      }

      $arguments = [];
      foreach ($definition['fields'] as $name => $argument) {
        $type = $this->buildFieldType($schemaBuilder, $argument);

        if ($type instanceof TypeInterface) {
          $config = [
            'name' => $name,
            'type' => $type,
          ];

          if (is_array($argument) && isset($argument['default'])) {
            $config['defaultValue'] = $argument['default'];
          }

          $arguments[$name] = new Field($config);
        }
      }

      return $arguments;
    }

    return [];
  }

  /**
   * Build the field type.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   * @param array|string $field
   *   The field definition array or type name.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildFieldType(PluggableSchemaBuilderInterface $schemaBuilder, $field) {
    if (is_array($field) && array_key_exists('data_type', $field) && $field['data_type']) {
      $types = array_map(function (TypeSystemPluginInterface $plugin) use ($schemaBuilder) {
        return $plugin->getDefinition($schemaBuilder);
      }, $schemaBuilder->find(function($definition) use ($field) {
        return array_key_exists('data_type', $definition) && $definition['data_type'] === $field['data_type'];
      }, [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
      ]));

      $type = array_pop($types) ?: $schemaBuilder->findByName('String', [GRAPHQL_SCALAR_PLUGIN])->getDefinition($schemaBuilder);
    }
    else {
      $typeInfo = is_array($field) ? $field['type'] : $field;

      $type = is_array($typeInfo) ? $this->buildEnumConfig($typeInfo, $field['enum_type_name']) : $schemaBuilder->findByName($typeInfo, [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
        GRAPHQL_ENUM_PLUGIN,
      ])->getDefinition($schemaBuilder);
    }

    if (isset($type) && $type instanceof TypeInterface) {
      $nullable = is_array($field) && array_key_exists('nullable', $field) && $field['nullable'];
      $multi = is_array($field) && array_key_exists('multi', $field) && $field['multi'];

      return $this->decorateType($type, $nullable, $multi);
    }

    return NULL;
  }
}
