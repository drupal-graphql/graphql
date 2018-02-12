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
      $definition = $this->getPluginDefinition();
      $typeNames = $definition['types'];
      $unionName = $this->buildName();

      $this->definition = new UnionType($this, $schemaBuilder, [
        'name' => $unionName,
        'description' => $this->buildDescription(),
        'types' => $this->buildTypes($schemaBuilder, $unionName, $typeNames),
      ]);
    }

    return $this->definition;
  }

  /**
   * Builds the list of types that are contained within this union type.
   *
   * Collects types that are explicitly referenced by this union type or that
   * are implicitly assigned by the type itself.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   The schema manager.
   * @param $unionName
   *   The name of this plugin.
   * @param $typeNames
   *   List of types that this union contains explicitly.
   *
   * @return \Drupal\graphql\GraphQL\Type\ObjectType[]
   *   An array of types to add to this union type.
   */
  protected function buildTypes(PluggableSchemaBuilderInterface $schemaBuilder, $unionName, $typeNames) {
    /** @var \Drupal\graphql\GraphQL\Type\ObjectType[] $types */
    $types = array_map(function (TypeSystemPluginInterface $type) use ($typeNames, $schemaBuilder) {
      return $type->getDefinition($schemaBuilder);
    }, $schemaBuilder->find(function ($type) use ($typeNames, $unionName) {
      return in_array($unionName, $type['unions']) || in_array($type['name'], $typeNames);
    }, [
      GRAPHQL_TYPE_PLUGIN,
    ]));

    return $types;
  }

}
