<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql_core\GraphQL\Traits\PluginTrait;
use Drupal\graphql_core\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Config\Object\InputObjectTypeConfig;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Base class for GraphQL interface plugins.
 */
abstract class InputTypePluginBase extends AbstractInputObjectType implements GraphQLPluginInterface, CacheableDependencyInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use TypedPluginTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager) {
    $this->config = new InputObjectTypeConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'fields' => $this->buildFields($schemaManager),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // May be overridden, but not required any more.
  }

  /**
   * Build the field list.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Field\FieldInterface[]
   *   The list of fields.
   */
  protected function buildFields(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (!$definition['fields']) {
        return [];
      }

      $arguments = [];
      foreach ($definition['fields'] as $name => $argument) {
        $type = $this->buildFieldType($schemaManager, $argument);

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
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   * @param array|string $field
   *   The field definition array or type name.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface
   *   The type object.
   */
  protected function buildFieldType(GraphQLSchemaManagerInterface $schemaManager, $field) {
    if (is_array($field) && array_key_exists('data_type', $field) && $field['data_type']) {
      $types = $schemaManager->find(function ($definition) use ($field) {
        return array_key_exists('data_type', $definition) && $definition['data_type'] === $field['data_type'];
      }, [
        GRAPHQL_CORE_INPUT_TYPE_PLUGIN,
        GRAPHQL_CORE_SCALAR_PLUGIN,
      ]);

      $type = array_pop($types) ?: $schemaManager->findByName('String', [GRAPHQL_CORE_SCALAR_PLUGIN]);
    }
    else {
      $typeInfo = is_array($field) ? $field['type'] : $field;

      $type = is_array($typeInfo) ? $this->buildEnumConfig($typeInfo, $field['name']) : $schemaManager->findByName($typeInfo, [
        GRAPHQL_CORE_INPUT_TYPE_PLUGIN,
        GRAPHQL_CORE_SCALAR_PLUGIN,
        GRAPHQL_CORE_ENUM_PLUGIN,
      ]);
    }

    if (isset($type) && $type instanceof TypeInterface) {
      $nullable = is_array($field) && array_key_exists('nullable', $field) && $field['nullable'];
      $multi = is_array($field) && array_key_exists('multi', $field) && $field['multi'];

      return $this->decorateType($type, $nullable, $multi);
    }

    return NULL;
  }
}
