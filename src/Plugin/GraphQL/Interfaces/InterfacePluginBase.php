<?php

namespace Drupal\graphql\Plugin\GraphQL\Interfaces;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Object\InterfaceTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

/**
 * Base class for GraphQL interface plugins.
 */
abstract class InterfacePluginBase extends AbstractInterfaceType implements TypeSystemPluginInterface, ContainerFactoryPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;
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
    $name = $this->getPluginDefinition()['name'];
    $types = array_filter($this->schemaManager->find(function ($type) use ($name) {
      return in_array($name, $type['interfaces']);
    }, [GRAPHQL_TYPE_PLUGIN]), function (TypePluginBase $type) use ($object) {
      return $type->applies($object);
    });

    return array_shift($types);
  }

}
