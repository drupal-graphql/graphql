<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql_core\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql_core\GraphQL\Traits\PluginTrait;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

/**
 * Base class for GraphQL type plugins.
 */
abstract class TypePluginBase extends AbstractObjectType implements GraphQLPluginInterface, CacheableDependencyInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait {
    buildFields as buildAttachedFields;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(GraphQLSchemaManagerInterface $schemaManager) {
    $this->config = new ObjectTypeConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'interfaces' => $this->buildInterfaces($schemaManager),
      'fields' => $this->buildFields($schemaManager),
    ]);
    $this->build($this->config);
  }

  /**
   * Build the list of interfaces.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Type\AbstractInterfaceTypeInterface[]
   *   The list of interfaces.
   */
  protected function buildInterfaces(GraphQLSchemaManagerInterface $schemaManager) {
    $definition = $this->getPluginDefinition();
    if ($definition['interfaces']) {
      return array_filter($schemaManager->find(function ($interface) use ($definition) {
        return in_array($interface['name'], $definition['interfaces']);
      }, [GRAPHQL_CORE_INTERFACE_PLUGIN]), function ($interface) {
        return $interface instanceof AbstractInterfaceType;
      });
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildFields(GraphQLSchemaManagerInterface $schemaManager) {
    $interfaceFields = array_reduce(array_map(function (AbstractInterfaceType $interface) {
      return $interface->getFields();
    }, $this->buildInterfaces($schemaManager)), 'array_merge', []);
    $attachedFields = $this->buildAttachedFields($schemaManager);
    return array_merge($interfaceFields, $attachedFields);
  }
}
