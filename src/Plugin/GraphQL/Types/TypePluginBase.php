<?php

namespace Drupal\graphql\Plugin\GraphQL\Types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

abstract class TypePluginBase extends PluginBase implements TypePluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    return new ObjectType([
      'name' => $definition['name'],
      'description' => $definition['description'],
      'contexts' => $definition['contexts'],
      'fields' => function () use ($builder, $definition) {
        $fields = $builder->getFields($definition['name']);

        if (!empty($definition['interfaces'])) {
          $inherited = array_map(function ($name) use ($builder) {
            return $builder->getFields($name);
          }, $definition['interfaces']);

          $inherited = call_user_func_array('array_merge', $inherited);
          return array_merge($inherited, $fields);
        }

        return $fields;
      },
      'interfaces' => function () use ($builder, $definition) {
        return array_filter(array_map(function ($name) use ($builder) {
          return $builder->getType($name);
        }, $definition['interfaces']), function ($type) {
          return $type instanceof InterfaceType;
        });
      },
      'isTypeOf' => function ($object, $context, ResolveInfo $info) use ($manager, $id) {
        $instance = $manager->getInstance(['id' => $id]);
        return $instance->applies($object, $context, $info);
      },
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'name' => $definition['name'],
      'description' => $this->buildDescription($definition),
      'interfaces' => $this->buildInterfaces($definition),
      'unions' => $this->buildUnions($definition),
      'contexts' => $this->buildCacheContexts($definition),
      'weight' => $definition['weight'],
    ];
  }

  /**
   * Builds the list of interfaces that this type implements.
   *
   * @param array $definition
   *   The plugin definition array.
   *
   * @return array
   *   The list of interfaces implemented by this type.
   */
  protected function buildInterfaces($definition) {
    return array_unique($definition['interfaces']);
  }

  /**
   * Builds the list of unions that this type belongs to.
   *
   * @param array $definition
   *   The plugin definition array.
   *
   * @return array
   *   The list of unions that this type belongs to.
   */
  protected function buildUnions($definition) {
    return array_unique($definition['unions']);
  }

  /**
   * Checks whether this type applies to a given object.
   *
   * @param mixed $object
   *   The object to check against.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return null|bool
   *   TRUE if this type applies to the given object or FALSE if it doesn't.
   */
  public function applies($object, ResolveContext $context, ResolveInfo $info) {
    return NULL;
  }

}
