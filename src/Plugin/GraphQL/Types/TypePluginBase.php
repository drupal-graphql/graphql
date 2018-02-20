<?php

namespace Drupal\graphql\Plugin\GraphQL\Types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
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
  public static function createInstance(SchemaBuilder $builder, TypePluginManager $manager, $definition, $id) {
    return new ObjectType([
      'name' => $definition['name'],
      'description' => $definition['description'],
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
    ];
  }

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildInterfaces($definition) {
    return array_unique($definition['interfaces']);
  }

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildUnions($definition) {
    return array_unique($definition['unions']);
  }

  /**
   * Checks whether this type applies to a given object.
   *
   * @param mixed $object
   *   The object to check against.
   * @param mixed $context
   *   The execution context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return null|bool
   *   TRUE if this type applies to the given object or FALSE if it doesn't.
   */
  public function applies($object, $context, ResolveInfo $info) {
    return NULL;
  }

}
