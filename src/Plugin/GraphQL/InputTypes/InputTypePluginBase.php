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
use Youshido\GraphQL\Field\Field;

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
      $this->definition = new InputObjectType($this, $schemaBuilder, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
      ]);

      $this->definition->addFields($this->buildFields($schemaBuilder));
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
    $arguments = [];

    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();

      foreach ($definition['fields'] as $name => $argument) {
        $type = $this->buildFieldType($schemaBuilder, $argument);
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
    $type = is_array($field) ? $field['type'] : $field;
    return $this->parseType($type, function ($type) use ($schemaBuilder) {
      return $schemaBuilder->findByDataTypeOrName($type, [
        GRAPHQL_INPUT_TYPE_PLUGIN,
        GRAPHQL_SCALAR_PLUGIN,
        GRAPHQL_ENUM_PLUGIN,
      ])->getDefinition($schemaBuilder);
    });
  }
}
