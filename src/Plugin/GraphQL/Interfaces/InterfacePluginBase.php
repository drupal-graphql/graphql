<?php

namespace Drupal\graphql\Plugin\GraphQL\Interfaces;

use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Object\InterfaceTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

/**
 * Base class for GraphQL interface plugins.
 */
abstract class InterfacePluginBase extends AbstractInterfaceType implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;

  /**
   * The list of types that implement this interface.
   *
   * @var \Youshido\GraphQL\Type\Object\AbstractObjectType[]
   */
  protected $types = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
    $this->config = new InterfaceTypeConfig([
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
   * Builds the list of types that are contained within this union type.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase $type
   *   The name of this plugin.
   */
  public function addType(TypePluginBase $type) {
    $this->types[] = $type;
  }

  /**
   * Default implementation of "resolveType".
   *
   * Checks all implementing types and returns the matching type with the
   * highest weight.
   *
   * @param mixed $object
   *   The current response tree value.
   *
   * @return \Youshido\GraphQL\Type\Object\AbstractObjectType|null
   *   The type object.
   */
  public function resolveType($object) {
    foreach ($this->types as $type) {
      if ($type instanceof TypePluginBase && $type->applies($object)) {
        return $type;
      }
    }

    return NULL;
  }

}
