<?php

namespace Drupal\graphql\Plugin\GraphQL\Unions;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Type\UnionType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

/**
 * Base class for GraphQL union type plugins.
 */
abstract class UnionTypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use NamedPluginTrait;

  /**
   * The type instance.
   *
   * @var \Drupal\graphql\GraphQL\Type\UnionType
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $name = $this->buildName();

      $this->definition = new UnionType($this, [
        'name' => $name,
        'description' => $this->buildDescription(),
        'types' => $this->buildTypes($schemaBuilder, $name),
      ]);
    }

    return $this->definition;
  }

  /**
   * Builds the list of types that are contained within this union type.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   The schema manager.
   * @param $name
   *   The name of this plugin.
   *
   * @return \Drupal\graphql\GraphQL\Type\ObjectType[]
   *   An array of types to add to this union type.
   */
  protected function buildTypes(PluggableSchemaBuilderInterface $schemaBuilder, $name) {
    /** @var \Drupal\graphql\GraphQL\Type\ObjectType[] $types */
    $types = array_map(function (TypeSystemPluginInterface $type) use ($schemaBuilder) {
      return $type->getDefinition($schemaBuilder);
    }, $schemaBuilder->find(function ($type) use ($name) {
      return in_array($name, $type['unions']);
    }, [
      GRAPHQL_TYPE_PLUGIN,
    ]));

    return $types;
  }

}
