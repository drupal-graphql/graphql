<?php

namespace Drupal\graphql\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

/**
 * Base class for GraphQL type plugins.
 */
abstract class TypePluginBase extends AbstractObjectType implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;

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
    $interfaces = $this->buildInterfaces($schemaManager);

    $this->config = new ObjectTypeConfig([
      'name' => $this->buildName(),
      'description' => $this->buildDescription(),
      'interfaces' => $interfaces,
      'fields' => $this->buildFields($schemaManager),
    ]);

    foreach ($interfaces as $interface) {
      if ($interface instanceof InterfacePluginBase) {
        $interface->addType($this);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // May be overridden, but not required any more.
  }

  /**
   * Build the list of interfaces.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface $schemaManager
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Type\AbstractInterfaceTypeInterface[]
   *   The list of interfaces.
   */
  protected function buildInterfaces(SchemaBuilderInterface $schemaManager) {
    $definition = $this->getPluginDefinition();
    if ($definition['interfaces']) {
      return array_filter($schemaManager->find(function($interface) use ($definition) {
        return in_array($interface['name'], $definition['interfaces']);
      }, [GRAPHQL_INTERFACE_PLUGIN]), function($interface) {
        return $interface instanceof AbstractInterfaceType;
      });
    }

    return [];
  }

  /**
   * Check if a value conforms to this type.
   *
   * @param mixed $value
   *   The current value.
   *
   * @return boolean
   *   TRUE if the type applies, else false.
   */
  public function applies($value) {
    return FALSE;
  }

}
