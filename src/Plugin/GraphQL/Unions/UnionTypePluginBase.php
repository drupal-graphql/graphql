<?php

namespace Drupal\graphql\Plugin\GraphQL\Unions;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Object\UnionTypeConfig;
use Youshido\GraphQL\Type\Union\AbstractUnionType;

/**
 * Base class for GraphQL union type plugins.
 */
abstract class UnionTypePluginBase extends AbstractUnionType implements TypeSystemPluginInterface, ContainerFactoryPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use DependencySerializationTrait;

  /**
   * The schema manager.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('graphql.pluggable_schema_manager'));
  }


  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, PluggableSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(PluggableSchemaManagerInterface $schemaManager) {
    $name = $this->buildName();

    $this->config = new UnionTypeConfig([
      'name' => $name,
      'description' => $this->buildDescription(),
      'types' => $this->buildTypes($name),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // May be overridden, but not required any more.
  }

  /**
   * @param $name
   *   The name of this plugin.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase[]
   *   An array of types to add to this union type.
   */
  protected function buildTypes($name) {
    /** @var \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase[] $types */
    $types = $this->schemaManager->find(function ($type) use ($name) {
      return in_array($name, $type['unions']);
    }, [
      GRAPHQL_TYPE_PLUGIN,
    ]);

    $types = array_merge($this->getPluginDefinition()['parents'], $types);
    return array_unique($types);
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
   * @return \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase
   *   The type object.
   */
  public function resolveType($object) {
    /** @var \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase $type */
    foreach ($this->getTypes() as $type) {
      if ($type->applies($object)) {
        return $type;
      }
    }

    return NULL;
  }

  /**
   * Default implementation of "getTypes".
   *
   * @return \Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase[]
   *   The types contained within this union type.
   */
  public function getTypes() {
    return $this->getConfig()->get('types', []);
  }

}
