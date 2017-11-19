<?php

namespace Drupal\graphql\Plugin\GraphQL\Types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Type\InterfaceType;
use Drupal\graphql\GraphQL\Type\ObjectType;
use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\FieldablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\NamedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Base class for GraphQL type plugins.
 */
abstract class TypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use NamedPluginTrait;
  use FieldablePluginTrait;

  /**
   * The type instance.
   *
   * @var \Drupal\graphql\GraphQL\Type\ObjectType
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $interfaces = $this->buildInterfaces($schemaBuilder);

      $this->definition = new ObjectType($this, [
        'name' => $this->buildName(),
        'description' => $this->buildDescription(),
        'interfaces' => $interfaces,
      ]);

      $this->definition->addFields($this->buildFields($schemaBuilder));

      foreach ($interfaces as $interface) {
        if ($interface instanceof InterfaceType) {
          $interface->registerType($this->definition, $this->getPluginDefinition()['weight']);
        }
      }
    }

    return $this->definition;
  }

  /**
   * Build the list of interfaces.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   Instance of the schema manager to resolve dependencies.
   *
   * @return \Youshido\GraphQL\Type\AbstractInterfaceTypeInterface[]
   *   The list of interfaces.
   */
  protected function buildInterfaces(PluggableSchemaBuilderInterface $schemaBuilder) {
    $definition = $this->getPluginDefinition();
    if ($definition['interfaces']) {
      return array_map(function (TypeSystemPluginInterface $interface) use ($schemaBuilder) {
        return $interface->getDefinition($schemaBuilder);
      }, array_filter($schemaBuilder->find(function ($interface) use ($definition) {
        return in_array($interface['name'], $definition['interfaces']);
      }, [GRAPHQL_INTERFACE_PLUGIN]), function ($interface) {
        return $interface instanceof InterfacePluginBase;
      }));
    }

    return [];
  }

  /**
   * Checks whether this type applies to a given object.
   *
   * @param mixed $object
   *   The object to check against.
   * @param \Youshido\GraphQL\Execution\ResolveInfo|null $info
   *   The resolve info object.
   *
   * @return bool
   *   TRUE if this type applies to the given object, FALSE otherwise.
   */
  public function applies($object, ResolveInfo $info = NULL) {
    return FALSE;
  }

}
